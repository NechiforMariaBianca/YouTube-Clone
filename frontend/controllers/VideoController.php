<?php

namespace frontend\controllers;

use common\models\Video;
use common\models\VideoLike;
use common\models\VideoView;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class VideoController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['like', 'dislike', 'history'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ],
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'like' => ['post'],
                    'dislike' => ['post'],
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $this->layout = 'main';
        $dataProvider = new ActiveDataProvider([
            'query' => Video::find()->with('createdBy')->published()->latest(),
            'pagination' => [
                'pageSize' =>5
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionView($id)
    {
        $this->layout = 'auth';
        $video = $this->findVideo($id);

        $videoView = new VideoView();
        $videoView->video_id = $id;
        $videoView->user_id = \Yii::$app->user->id;
        $videoView->created_at = time();
        $videoView->save();

        $similarVideos = Video::find()
            ->published()
            ->byKeyword($video->title)
            ->andWhere(['NOT', ['video_id' => $id]])
            ->limit(10)
            ->all();

        return $this->render('view', [
            'model' => $video,
            'similarVideos' => $similarVideos
        ]);
    }

    public function actionLike($id)
    {
        $video = $this->findVideo($id);
        $userId = \Yii::$app->user->id;

        $videoLikeDislike = VideoLike::find()
            ->userIdVideoId($userId, $id)
            ->one();
        if (!$videoLikeDislike) {
            $this->saveLikeDislike($id, $userId, VideoLike::TYPE_LIKE);
        } else if ($videoLikeDislike->type == VideoLike::TYPE_LIKE) {
            $videoLikeDislike->delete();
        } else {
            $videoLikeDislike->delete();
            $this->saveLikeDislike($id, $userId, VideoLike::TYPE_LIKE);
        }

        return $this->renderAjax('_buttons', [
            'model' => $video
        ]);

    }

    public function actionDislike($id)
    {
        $video = $this->findVideo($id);
        $userId = \Yii::$app->user->id;

        $videoLikeDislike = VideoLike::find()
            ->userIdVideoId($userId, $id)
            ->one();
        if (!$videoLikeDislike) {
            $this->saveLikeDislike($id, $userId, VideoLike::TYPE_DISLIKE);
        } else if ($videoLikeDislike->type == VideoLike::TYPE_DISLIKE) {
            $videoLikeDislike->delete();
        } else {
            $videoLikeDislike->delete();
            $this->saveLikeDislike($id, $userId, VideoLike::TYPE_DISLIKE);
        }

        return $this->renderAjax('_buttons', [
            'model' => $video
        ]);
    }

    public function actionSearch($keyword)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $dataToSend = [];

        $this->layout = 'main';
        $query = Video::find()
            ->with('createdBy')
            ->published()
            ->latest();
        $videos = Video::find()->where(['LIKE','title' ,"%{$keyword}%", false])->andWhere(['LIKE', 'description', "%{$keyword}%", false])->all();
        foreach ($videos as $video) {
            $dataToSend[] = [
                'title' => $video->title,
                'id' => $video->video_id
            ];
        }
        return $dataToSend;

//        if ($keyword) {
//            $query->byKeyword($keyword);
//        }
//        $dataProvider = new ActiveDataProvider([
//            'query' => $query
//        ]);
//
//        return $this->render('search', [
//            'dataProvider' => $dataProvider
//        ]);
    }

    public function actionHistory()
    {
        $query = Video::find()
            ->alias('v')
            ->innerJoin("(SELECT video_id, MAX(created_at) as max_date FROM video_view
                WHERE user_id = :userId
                GROUP BY video_id) vv", 'vv.video_id = v.video_id', [
                    'userId' =>\Yii::$app->user->id
            ])
        ->orderBy("vv.max_date DESC");

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $this->render('history', [
            'dataProvider' => $dataProvider
        ]);
    }

    protected function findVideo($id)
    {
        $video = Video::findOne($id);
        if (!$video) {
            throw new NotFoundHttpException("Video does not exist");
        }
        return $video;
    }

    protected function saveLikeDislike($video_id, $user_id, $type)
    {
        $videoLikeDislike = new VideoLike();
        $videoLikeDislike->video_id = $video_id;
        $videoLikeDislike->user_id = $user_id;
        $videoLikeDislike->type = $type;
        $videoLikeDislike->created_at = time();
        $videoLikeDislike->save();
    }
}