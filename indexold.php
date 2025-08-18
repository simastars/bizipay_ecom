<?php require_once('header.php'); 
require_once("admin/inc/config.php");
?>

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
                        <div class="slide-text <?php if($row['position'] == 'Left') {echo 'slide_style_left';} elseif($row['position'] == 'Center') {echo 'slide_style_center';} elseif($row['position'] == 'Right') {echo 'slide_style_right';} ?>" style="display: block;">
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


<div class="product pt_70 pb_70">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="featured-tab" data-toggle="tab" href="#featured" role="tab" aria-controls="featured" aria-selected="true">Featured</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="latest-tab" data-toggle="tab" href="#latest" role="tab" aria-controls="latest" aria-selected="false">Latest</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="popular-tab" data-toggle="tab" href="#popular" role="tab" aria-controls="popular" aria-selected="false">Popular</a>
                    </li>
                </ul>
                <div class="tab-content pt-4" id="productTabsContent">
                    <div class="tab-pane fade show active" id="featured" role="tabpanel" aria-labelledby="featured-tab">
                        <div class="product-carousel" id="featured-products"></div>
                        <div id="featured-loading" style="text-align:center;display:none;">
                            <img src="assets/img/loading.gif" alt="Loading..." style="width:40px;">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="latest" role="tabpanel" aria-labelledby="latest-tab">
                        <div class="product-carousel" id="latest-products"></div>
                        <div id="latest-loading" style="text-align:center;display:none;">
                            <img src="assets/img/loading.gif" alt="Loading..." style="width:40px;">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="popular" role="tabpanel" aria-labelledby="popular-tab">
                        <div class="product-carousel" id="popular-products"></div>
                        <div id="popular-loading" style="text-align:center;display:none;">
                            <img src="assets/img/loading.gif" alt="Loading..." style="width:40px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<?php require_once('footer.php'); ?>

<!-- Ensure consistent product grid for all tabs -->
<style>
/* Stronger rules to override carousel/plugin CSS */
.product-carousel,
#featured-products,
#latest-products,
#popular-products {
  display: flex !important;
  flex-wrap: wrap !important;
  gap: 20px !important;
}
.product-carousel .item,
#featured-products .item,
#latest-products .item,
#popular-products .item {
  display: block !important;
  flex: 0 0 23% !important; /* 4 items per row on desktop */
  max-width: 23% !important;
  box-sizing: border-box !important;
  margin-bottom: 20px !important;
}
.product-carousel .item .thumb,
#featured-products .item .thumb,
#latest-products .item .thumb,
#popular-products .item .thumb {
  width: 100% !important;
  height: 220px !important;
  overflow: hidden !important;
}
.product-carousel .item .photo,
#featured-products .item .photo,
#latest-products .item .photo,
#popular-products .item .photo {
  width: 100% !important;
  height: 100% !important;
  background-size: cover !important;
  background-position: center center !important;
}
@media (max-width: 991px) {
  .product-carousel .item,
  #featured-products .item,
  #latest-products .item,
  #popular-products .item { flex: 0 0 48% !important; max-width:48% !important; }
}
@media (max-width: 575px) {
  .product-carousel .item,
  #featured-products .item,
  #latest-products .item,
  #popular-products .item { flex: 0 0 100% !important; max-width:100% !important; }
}
</style>

<!-- Infinite Scroll JS + Tabs -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
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
        $(loading).show();
        $.get('products_ajax.php', {type:type, offset:offset, limit:limit}, function(data){
            console.log('AJAX response for', type, 'offset', offset, 'limit', limit, ':', data);
            // Remove whitespace and invisible characters
            var cleanData = data.replace(/\s+/g, '');
            if(cleanData === '' || data === null) {
                setEnd(true);
            } else {
                $(container).append(data);
                // Force visibility for debugging
                $(container + ' .item').css({display: 'block', opacity: 1, position: 'static', visibility: 'visible'});
                setOffset(offset+limit);
            }
            $(loading).hide();
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