<?php

include("admin/inc/config.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;

$html = '';

if ($type == 'featured') {
    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_is_featured=? AND p_is_active=? LIMIT $offset, $limit");
    $statement->execute(array(1,1));
} elseif ($type == 'latest') {
    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_is_active=? ORDER BY p_id DESC LIMIT $offset, $limit");
    $statement->execute(array(1));
} elseif ($type == 'popular') {
    $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_is_active=? ORDER BY p_total_view DESC LIMIT $offset, $limit");
    $statement->execute(array(1));
} else {
    echo '';
    exit;
}
?>
<style>

</style>
<?php
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    ob_start();
    ?>
    <div class="item" data-pid="<?php echo htmlspecialchars($row['p_id'], ENT_QUOTES); ?>">
        <div class="thumb">
            <div class="photo" style="background-image:url(assets/uploads/<?php echo htmlspecialchars($row['p_featured_photo'], ENT_QUOTES); ?>);"></div>
            <div class="overlay"></div>
        </div>
        <div class="text">
            <h3><a href="product.php?id=<?php echo $row['p_id']; ?>"><?php echo htmlspecialchars($row['p_name'], ENT_QUOTES); ?></a></h3>
            <h4>
                $<?php echo htmlspecialchars($row['p_current_price'], ENT_QUOTES); ?>
                <?php if($row['p_old_price'] != ''): ?>
                <del>
                    $<?php echo htmlspecialchars($row['p_old_price'], ENT_QUOTES); ?>
                </del>
                <?php endif; ?>
            </h4>
            <div class="rating">
                <?php
                $t_rating = 0;
                $statement1 = $pdo->prepare("SELECT * FROM tbl_rating WHERE p_id=?");
                $statement1->execute(array($row['p_id']));
                $tot_rating = $statement1->rowCount();
                if($tot_rating == 0) {
                    $avg_rating = 0;
                } else {
                    $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($result1 as $row1) {
                        $t_rating = $t_rating + $row1['rating'];
                    }
                    $avg_rating = $t_rating / $tot_rating;
                }
                ?>
                <?php
                if($avg_rating == 0) {
                    echo '';
                }
                elseif($avg_rating == 1.5) {
                    echo '\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star-half-o"></i>\n                                            <i class="fa fa-star-o"></i>\n                                            <i class="fa fa-star-o"></i>\n                                            <i class="fa fa-star-o"></i>\n                                        ';
                }
                elseif($avg_rating == 2.5) {
                    echo '\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star-half-o"></i>\n                                            <i class="fa fa-star-o"></i>\n                                            <i class="fa fa-star-o"></i>\n                                        ';
                }
                elseif($avg_rating == 3.5) {
                    echo '\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star-half-o"></i>\n                                            <i class="fa fa-star-o"></i>\n                                        ';
                }
                elseif($avg_rating == 4.5) {
                    echo '\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star"></i>\n                                            <i class="fa fa-star-half-o"></i>\n                                        ';
                }
                else {
                    for($i=1;$i<=5;$i++) {
                        ?>
                        <?php if($i>$avg_rating): ?>
                            <i class="fa fa-star-o"></i>
                        <?php else: ?>
                            <i class="fa fa-star"></i>
                        <?php endif; ?>
                        <?php
                    }
                }
                ?>
            </div>

            <?php if($row['p_qty'] == 0): ?>
                <div class="out-of-stock">
                    <div class="inner">
                        Out Of Stock
                    </div>
                </div>
            <?php else: ?>
                <p><a href="product.php?id=<?php echo $row['p_id']; ?>"><i class="fa fa-shopping-cart"></i> Add to Cart</a></p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    $html .= ob_get_clean();
}
echo $html;
