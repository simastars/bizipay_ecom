<?php require_once('header.php'); ?>
<style>
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 2rem;
}
@import url('https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap');

/*-- VARIABLES CSS--*/
/*Colores*/
:root{
    --first-color: #E3F8FF;
    --second-color: #DCFAFB;
    --third-color: #FFE8DF;
    --accent-color: #FF5151;
    --dark-color: #161616;
}

/*Tipografia responsive*/
:root{
    --body-font: 'Open Sans';
    --h1-font-size: 1.5rem;
    --h3-font-size: 1rem;
    --normal-font-size: 0.938rem;
    --smaller-font-size: 0.75rem;
}
@media screen and (min-width: 768px){
    :root{
        --h1-font-size: 2rem;
        --normal-font-size: 1rem;
        --smaller-font-size: 0.813rem;
    }
}

/*-- BASE --*/
*,::after,::before{
    box-sizing: border-box;
}

h1{
    font-size: var(--h1-font-size);
}
img{
    max-width: 100%;
    height: auto;
}
a{
    text-decoration: none;
}

/*-- LAYAOUT --*/
.main {
    padding: 2rem 0;
}
.bd-grid{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    max-width: 1200px;
    margin-left: 2.5rem;
    margin-right: 2.5rem;
    align-items: center;
    gap: 2rem;
}

/*-- PAGES --*/
.title-shop{
    position: relative;
    margin: 0 2.5rem;
}
.title-shop::after{
    content: '';
    position: absolute;
    top: 50%;
    width: 72px;
    height: 2px;
    background-color: var(--dark-color);
    margin-left: .25rem;
}

/*-- COMPONENT --*/
.card{
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem 2rem;
    border-radius: 1rem;
    overflow: hidden;
}
article:nth-child(1){
    background-color: var(--first-color);
}
article:nth-child(2){
    background-color: var(--second-color);
}
article:nth-child(3){
    background-color: var(--third-color);
}
article:nth-child(4){
    background-color: var(--second-color);
}
.card__img{
    width: 180px;
    height: auto;
    padding: 3rem 0;
    transition: .5s;
}
.card__name{
    position: absolute;
    left: -25%;
    top: 0;
    width: 3.5rem;
    height: 100%;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    text-align: center;
    background-color: var(--dark-color);
    color: #fff;
    font-weight: bold;
    transition: .5s;
}
.card__icon{
    font-size: 1.5rem;
    color: var(--dark-color);
}
.card__icon:hover{
    color: var(--accent-color);
}
.card__precis{
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    transition: .5s;
}
.card__preci{
    display: block;
    text-align: center;
}
.card__preci--before{
    font-size: var(--smaller-font-size);
    color: var(--accent-color);
    margin-bottom: .25rem;
}
.card__preci--now{
    font-size: var(--h3-font-size);
    font-weight: bold;
}
/*Move left*/
.card:hover{
    box-shadow: 0 .5rem 1rem #D1D9E6;
}
.card:hover .card__name{
    left: 0;
}
.card:hover .card__img{
    transform: rotate(30deg);
    margin-left: 3.5rem;
}
.card:hover .card__precis{
    margin-left: 3.5rem;
    padding: 0 1.5rem;
}

/*-- FOOTER --*/

footer{
  text-align: center;
}

/*-- MEDIA QUERIES --*/
@media screen and (min-width: 1200px){
    body{
        margin: 3rem 0 0 0;
    }
    .title-shop{
        margin: 0 5rem;
    }
    .bd-grid{
        margin-left: auto;
        margin-right: auto;
    }
}
    </style>
<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row)
{
    $cta_title = $row['cta_title'];
    $cta_content = $row['cta_content'];
    $cta_read_more_text = $row['cta_read_more_text'];
    $cta_read_more_url = $row['cta_read_more_url'];
    $cta_photo = $row['cta_photo'];
    $featured_product_title = $row['featured_product_title'];
    $featured_product_subtitle = $row['featured_product_subtitle'];
    $latest_product_title = $row['latest_product_title'];
    $latest_product_subtitle = $row['latest_product_subtitle'];
    $popular_product_title = $row['popular_product_title'];
    $popular_product_subtitle = $row['popular_product_subtitle'];
    $total_featured_product_home = $row['total_featured_product_home'];
    $total_latest_product_home = $row['total_latest_product_home'];
    $total_popular_product_home = $row['total_popular_product_home'];
    $home_service_on_off = $row['home_service_on_off'];
    $home_welcome_on_off = $row['home_welcome_on_off'];
    $home_featured_product_on_off = $row['home_featured_product_on_off'];
    $home_latest_product_on_off = $row['home_latest_product_on_off'];
    $home_popular_product_on_off = $row['home_popular_product_on_off'];

}


?>

<div id="bootstrap-touch-slider" class="carousel bs-slider fade control-round indicators-line" data-ride="carousel" data-pause="hover" data-interval="false" >

    <!-- Indicators -->
    <ol class="carousel-indicators">
        <?php
        $i=0;
        $statement = $pdo->prepare("SELECT * FROM tbl_slider");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {            
            ?>
            <li data-target="#bootstrap-touch-slider" data-slide-to="<?php echo $i; ?>" <?php if($i==0) {echo 'class="active"';} ?>></li>
            <?php
            $i++;
        }
        ?>
    </ol>

    <!-- Wrapper For Slides -->
    <div class="carousel-inner" role="listbox">

        <?php
        $i=0;
        $statement = $pdo->prepare("SELECT * FROM tbl_slider");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
        foreach ($result as $row) {            
            ?>
            <div class="item <?php if($i==0) {echo 'active';} ?>" style="background-image:url(assets/uploads/<?php echo $row['photo']; ?>);">
                <div class="bs-slider-overlay"></div>
                <div class="container">
                    <div class="row">
                        <div class="slide-text <?php if($row['position'] == 'Left') {echo 'slide_style_left';} elseif($row['position'] == 'Center') {echo 'slide_style_center';} elseif($row['position'] == 'Right') {echo 'slide_style_right';} ?>">
                            <h1 data-animation="animated <?php if($row['position'] == 'Left') {echo 'zoomInLeft';} elseif($row['position'] == 'Center') {echo 'flipInX';} elseif($row['position'] == 'Right') {echo 'zoomInRight';} ?>"><?php echo $row['heading']; ?></h1>
                            <p data-animation="animated <?php if($row['position'] == 'Left') {echo 'fadeInLeft';} elseif($row['position'] == 'Center') {echo 'fadeInDown';} elseif($row['position'] == 'Right') {echo 'fadeInRight';} ?>"><?php echo nl2br($row['content']); ?></p>
                            <a href="<?php echo $row['button_url']; ?>" target="_blank"  class="btn btn-primary" data-animation="animated <?php if($row['position'] == 'Left') {echo 'fadeInLeft';} elseif($row['position'] == 'Center') {echo 'fadeInDown';} elseif($row['position'] == 'Right') {echo 'fadeInRight';} ?>"><?php echo $row['button_text']; ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $i++;
        }
        ?>
    </div>

    <!-- Slider Left Control -->
    <a class="left carousel-control" href="#bootstrap-touch-slider" role="button" data-slide="prev">
        <span class="fa fa-angle-left" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>

    <!-- Slider Right Control -->
    <a class="right carousel-control" href="#bootstrap-touch-slider" role="button" data-slide="next">
        <span class="fa fa-angle-right" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>

</div>


<?php //if($home_service_on_off == 1): ?>
<!-- <div class="service bg-gray">
    <div class="container">
        <div class="row">
            <?php
                $statement = $pdo->prepare("SELECT * FROM tbl_service");
                $statement->execute();
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
                foreach ($result as $row) {
                    ?>
                    <div class="col-md-4">
                        <div class="item">
                            <div class="photo"><img src="assets/uploads/<?php echo $row['photo']; ?>" width="150px" alt="<?php echo $row['title']; ?>"></div>
                            <h3><?php echo $row['title']; ?></h3>
                            <p>
                                <?php echo nl2br($row['content']); ?>
                            </p>
                        </div>
                    </div>
                    <?php
                }
            ?>
        </div>
    </div>
</div> -->
<?php //endif; ?>

<?php
// ...existing code...
?>
<?php if($home_featured_product_on_off == 1 || $home_latest_product_on_off == 1 || $home_popular_product_on_off == 1): ?>

<!-- Products Tabs (Featured / Latest / Popular) -->
<div class="product pt_70 pb_70">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs product-tabs" role="tablist">
                    <?php if($home_featured_product_on_off == 1): ?>
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-featured" data-toggle="tab" href="#featured" role="tab" aria-controls="featured" aria-selected="true">
                                <?php echo $featured_product_title; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if($home_latest_product_on_off == 1): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php if($home_featured_product_on_off==0) echo 'active'; ?>" id="tab-latest" data-toggle="tab" href="#latest" role="tab" aria-controls="latest" aria-selected="false">
                                <?php echo $latest_product_title; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if($home_popular_product_on_off == 1): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php if($home_featured_product_on_off==0 && $home_latest_product_on_off==0) echo 'active'; ?>" id="tab-popular" data-toggle="tab" href="#popular" role="tab" aria-controls="popular" aria-selected="false">
                                <?php echo $popular_product_title; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content mt-4">
                    <?php if($home_featured_product_on_off == 1): ?>
                    <div class="tab-pane fade show active" id="featured" role="tabpanel" aria-labelledby="tab-featured">
                        <div class="headline">
                            <h2><?php echo $featured_product_title; ?></h2>
                            <h3><?php echo $featured_product_subtitle; ?></h3>
                        </div>
                        <div class="product-carousel">
                            <div id="featured-products" class="product-grid"></div>
                            <div id="featured-loading" class="text-center" style="display:none;margin:20px 0;">Loading...</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($home_latest_product_on_off == 1): ?>
                    <div class="tab-pane fade <?php if($home_featured_product_on_off==0) echo 'show active'; ?>" id="latest" role="tabpanel" aria-labelledby="tab-latest">
                        <div class="headline">
                            <h2><?php echo $latest_product_title; ?></h2>
                            <h3><?php echo $latest_product_subtitle; ?></h3>
                        </div>
                        <div class="product-carousel">
                            <div id="latest-products" class="product-grid"></div>
                            <div id="latest-loading" class="text-center" style="display:none;margin:20px 0;">Loading...</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($home_popular_product_on_off == 1): ?>
                    <div class="tab-pane fade <?php if($home_featured_product_on_off==0 && $home_latest_product_on_off==0) echo 'show active'; ?>" id="popular" role="tabpanel" aria-labelledby="tab-popular">
                        <div class="headline">
                            <h2><?php echo $popular_product_title; ?></h2>
                            <h3><?php echo $popular_product_subtitle; ?></h3>
                        </div>
                        <div class="product-carousel">
                            <div id="popular-products" class="product-grid"></div>
                            <div id="popular-loading" class="text-center" style="display:none;margin:20px 0;">Loading...</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Minimal CSS to keep grid consistent -->
<style>
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 2rem;
}
/* Override generic .item rules from carousels/plugins when inside our grid */
.product-grid > .item,
.product-grid > .product-item {
    position: static !important;
    display: block !important;
    width: auto !important;
    margin: 0 !important;
    box-sizing: border-box !important;
}

/* Ensure thumbnail image provides consistent intrinsic height for grid layout */
.product-grid .thumb { display:block; }
.product-grid .photo {
    width:100%;
    height:200px;
    background-size:cover;
    background-position:center center;
}
.product-grid .text { padding: 0.75rem 0; }
/* .product-grid {
  display:flex;
  flex-wrap:wrap;
  gap:20px;
} */
/* .product-grid .item, .product-grid .product-item {
  flex:0 0 23%;
  box-sizing:border-box;
  min-height:260px;
} */
/* @media (max-width:991px){ .product-grid .item { flex:0 0 48%; } }
@media (max-width:575px){ .product-grid .item { flex:0 0 100%; } } */
</style>

<!-- keep existing footer and scripts below -->
// ...existing code...




<?php require_once('footer.php'); ?>



<!-- Infinite Scroll JS + Tabs -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
    var featuredLimit = <?php echo (int)$total_featured_product_home; ?>;
    var latestLimit = <?php echo (int)$total_latest_product_home; ?>;
    var popularLimit = <?php echo (int)$total_popular_product_home; ?>;
    var featuredOffset = 0, latestOffset = 0, popularOffset = 0;
    var featuredEnd = false, latestEnd = false, popularEnd = false;
    var activeTab = 'featured';

    function loadProducts(type, offset, limit, container, loading, endFlag, setOffset, setEnd) {
        if(endFlag) return;
        // Normalize container and loading to jQuery objects so callers may pass selector string or jQuery object
        var $container = (container && container.jquery) ? container : $(container);
        var $loading = (loading && loading.jquery) ? loading : $(loading);
        // Prevent concurrent loads for the same section
        if($loading.length && $loading.is(':visible')) {
            console.log('Load already in progress for', type);
            return;
        }
        $loading.show();
        $.get('products_ajax.php', {type:type, offset:offset, limit:limit}, function(data){
            console.log('AJAX response for', type, 'offset', offset, 'limit', limit, ':', data);
            try {
                var s = (typeof data === 'string') ? data : String(data);
                // Remove whitespace and invisible characters for empty-check
                var cleanData = s.replace(/\s+/g, '');
                if(cleanData === '' || data === null) {
                    setEnd(true);
                } else {
                    $container.append(data);
                    // Target appended items via .find to avoid concatenating objects with strings
                    // $container.find('.product-item, .item').css({display: 'block', opacity: 1, position: 'static', visibility: 'visible'});
                    setOffset(offset+limit);
                }
            } catch(err) {
                console.error('Error processing AJAX response for', type, err);
            } finally {
                $loading.hide();
            }
        });
    }

    // Initial load for featured
    loadProducts('featured', featuredOffset, featuredLimit, '#featured-products', '#featured-loading', featuredEnd, function(v){featuredOffset=v;}, function(v){featuredEnd=v;});

    // Tab click event
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        activeTab = $(e.target).attr('aria-controls');
        if(activeTab === 'featured' && featuredOffset === 0 && !featuredEnd) {
            loadProducts('featured', featuredOffset, featuredLimit, '#featured-products', '#featured-loading', featuredEnd, function(v){featuredOffset=v;}, function(v){featuredEnd=v;});
        }
        if(activeTab === 'latest' && latestOffset === 0 && !latestEnd) {
            loadProducts('latest', latestOffset, latestLimit, '#latest-products', '#latest-loading', latestEnd, function(v){latestOffset=v;}, function(v){latestEnd=v;});
        }
        if(activeTab === 'popular' && popularOffset === 0 && !popularEnd) {
            loadProducts('popular', popularOffset, popularLimit, '#popular-products', '#popular-loading', popularEnd, function(v){popularOffset=v;}, function(v){popularEnd=v;});
        }
    });

    // Infinite scroll for active tab only
    $(window).on('scroll', function(){
        var $container, offset, limit, endFlag, setOffset, setEnd, type, loading;
        if(activeTab === 'featured') {
            $container = $('#featured-products'); offset = featuredOffset; limit = featuredLimit; endFlag = featuredEnd; setOffset = function(v){featuredOffset=v;}; setEnd = function(v){featuredEnd=v;}; type = 'featured'; loading = '#featured-loading';
        } else if(activeTab === 'latest') {
            $container = $('#latest-products'); offset = latestOffset; limit = latestLimit; endFlag = latestEnd; setOffset = function(v){latestOffset=v;}; setEnd = function(v){latestEnd=v;}; type = 'latest'; loading = '#latest-loading';
        } else if(activeTab === 'popular') {
            $container = $('#popular-products'); offset = popularOffset; limit = popularLimit; endFlag = popularEnd; setOffset = function(v){popularOffset=v;}; setEnd = function(v){popularEnd=v;}; type = 'popular'; loading = '#popular-loading';
        }
        if($container && $container.length && !endFlag && $(window).scrollTop() + $(window).height() > $container.offset().top + $container.height() - 200) {
            loadProducts(type, offset, limit, $container, loading, endFlag, setOffset, setEnd);
        }
    });
});
</script>
