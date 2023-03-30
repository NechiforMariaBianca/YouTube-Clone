<?php


use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\helpers\Url;

NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => Yii::$app->homeUrl,
    'options' => ['class' => 'navbar-expand-lg navbar-light bg-light shadow-sm'],
    'innerContainerOptions' => [
        'class' => 'container-fluid'
    ]
]);

if (Yii::$app->user->isGuest) {
    $menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
    $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
} else {
    $menuItems[] = [
        'label' => 'Logout (' . Yii::$app->user->identity->username . ')',
        'url' => ['/site/logout'],
        'linkOptions' => [
            'data-method' => 'post'
        ]
    ];
}
?>
<!--<form action="--><?php //echo Url::to(['/video/search']) ?><!--"-->
<!--      class="form-inline">-->
<!---->
<!--    <input class="mt-1"-->
<!--           style="border-radius: 5px; border: 1px solid grey; padding: 7px;"-->
<!--           size="50px" type="search"-->
<!--           placeholder="Search"-->
<!--           name="keyword"-->
<!--           value="--><?php //echo Yii::$app->request->get('keyword') ?><!--">-->
<!---->
<!--    <button class="mb-1 btn btn-outline-success">Search</button>-->
<!--</form>-->
    <input class="form-control mr-sm-2 col-1 keyword" type="search" placeholder="Search" name="keyword">
    <button class="btn btn-outline-success my-2 my-sm-0" onclick="search()">Search</button>
<?php
echo Nav::widget([
    'options' => ['class' => 'navbar-nav ml-auto'],
    'items' => $menuItems,
]);
NavBar::end();
?>
<script>
    function search() {
        let keyword = $('.keyword').val();

        $.ajax({
            type: "GET",
            url: '<?php echo Url::to(['/video/search']); ?>&keyword=' + keyword,
            data: {},
            success: function (data) {
                let res = Object.keys(data).map(function (key) { return data[key]; });
                $('.card').addClass('d-none');
                for (let i = 0; i < res.length; i++) {
                    $('#video_' + res[i].id).removeClass('d-none');
                }
            },
            error: function (error) {
                console.log(error)
            }
        });
    }
</script>
