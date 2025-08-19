<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row)
{
    $featured_product_title = $row['featured_product_title'];
    $featured_product_subtitle = $row['featured_product_subtitle'];
    $latest_product_title = $row['latest_product_title'];
    $latest_product_subtitle = $row['latest_product_subtitle'];
    $popular_product_title = $row['popular_product_title'];
    $popular_product_subtitle = $row['popular_product_subtitle'];
}
?>

<div class="container pt_70 pb_70">
    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-featured-link" data-toggle="tab" href="#tab-featured" role="tab" aria-controls="tab-featured" aria-selected="true"><?php echo $featured_product_title; ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-latest-link" data-toggle="tab" href="#tab-latest" role="tab" aria-controls="tab-latest" aria-selected="false"><?php echo $latest_product_title; ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-popular-link" data-toggle="tab" href="#tab-popular" role="tab" aria-controls="tab-popular" aria-selected="false"><?php echo $popular_product_title; ?></a>
                </li>
            </ul>

            <div class="tab-content mt_20" id="productTabsContent">
                <div class="tab-pane fade show active" id="tab-featured" role="tabpanel" aria-labelledby="tab-featured-link">
                    <div class="product-carousel"></div>
                    <div class="text-center mt_3">
                        <button class="btn btn-primary load-more" data-type="featured" data-offset="0" data-limit="8">Load more</button>
                        <div class="loader" style="display:none;margin-top:8px">Loading…</div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-latest" role="tabpanel" aria-labelledby="tab-latest-link">
                    <div class="product-carousel"></div>
                    <div class="text-center mt_3">
                        <button class="btn btn-primary load-more" data-type="latest" data-offset="0" data-limit="8">Load more</button>
                        <div class="loader" style="display:none;margin-top:8px">Loading…</div>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-popular" role="tabpanel" aria-labelledby="tab-popular-link">
                    <div class="product-carousel"></div>
                    <div class="text-center mt_3">
                        <button class="btn btn-primary load-more" data-type="popular" data-offset="0" data-limit="8">Load more</button>
                        <div class="loader" style="display:none;margin-top:8px">Loading…</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* reuse the grid rules so tabbed version looks identical */
.product-carousel{display:flex;flex-wrap:wrap;margin:0 -10px}
.product-carousel > .item{box-sizing:border-box;padding:10px;position:static !important;float:none !important;flex:0 0 25%;max-width:25%;width:25%}
@media (max-width:991px){.product-carousel > .item{flex:0 0 33.3333%;max-width:33.3333%;width:33.3333%}}
@media (max-width:767px){.product-carousel > .item{flex:0 0 50%;max-width:50%;width:50%}}
@media (max-width:479px){.product-carousel > .item{flex:0 0 100%;max-width:100%;width:100%}}
.product-carousel .photo{width:100%;height:220px;object-fit:cover;display:block}
/* ensure text is visible and overlay doesn't hide content */
.product-carousel .item .text{
    background: #fff;
    color: #222 !important;
    padding: 10px !important;
    position: relative !important;
    z-index: 3 !important;
}
.product-carousel .item .text a{color: inherit !important}
.product-carousel .item .thumb{position:relative}
.product-carousel .item .thumb .overlay{position:absolute;top:0;left:0;right:0;bottom:0;z-index:1;background:rgba(0,0,0,0)}
</style>

<?php require_once('footer.php'); ?>

<script>
(function($){
    function loadProducts(btn){
        var $btn = $(btn);
        var type = $btn.data('type');
        var offset = parseInt($btn.data('offset')) || 0;
        var limit = parseInt($btn.data('limit')) || 8;
        var $loader = $btn.siblings('.loader').first();
        var $container = $btn.closest('.tab-pane').find('.product-carousel').first();

        $btn.prop('disabled', true);
        $loader.show();

        $.get('products_ajax_tabbed.php', {type: type, offset: offset, limit: limit}, function(data){
            $loader.hide();
            $btn.prop('disabled', false);
            var $items = $(data);
            var appended = 0;
            $items.each(function(){
                var pid = $(this).data('pid');
                if(pid){
                    if($container.find('[data-pid="'+pid+'"]').length === 0){
                        $container.append(this);
                        appended++;
                    }
                } else {
                    $container.append(this);
                    appended++;
                }
            });

            if(appended < limit){
                $btn.hide();
            } else {
                $btn.data('offset', offset + limit);
            }
        }).fail(function(){
            $loader.hide();
            $btn.prop('disabled', false);
            alert('Could not load products');
        });
    }

    // click handler
    $(document).on('click', '.load-more', function(e){
        e.preventDefault();
        loadProducts(this);
    });

    // when a tab is shown, auto-load first page if empty
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        var $pane = $(target);
        var $btn = $pane.find('.load-more').first();
        var $container = $pane.find('.product-carousel').first();
        if($container.children().length === 0){
            // load first page
            $btn.trigger('click');
        }
    });

    // load featured on ready
    $(function(){
        $('#tab-featured').find('.load-more').trigger('click');
    });
})(jQuery);
</script>
