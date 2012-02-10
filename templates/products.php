<?php
use EMT\Wordpress\Util;

//$updateUrl = plugins_url( 'update.php', EMT_ABSPLUGIN);

?>
<script type="text/javascript">window.emt_product_UpdateUrl = '<?php echo $updateUrl ?>';</script>
<div class="wrap">

	<div id="icon-ementor" class="icon32"><br /></div>
	<h2>Produkty</h2>
<!--	<div class="tablenav top">-->
<!--		<input type="text" id="passName" placeholder="Imię i nazwisko klienta" />-->
<!--		<button type="button" id="newPass" class="button-secondary">-->
<!--			DODAJ OSOBĘ-->
<!--		</button>-->
<!--	</div>-->


	<table class="wp-list-table widefat fixed emt-products" cellspacing="0">
	<thead>
	<tr>
		<th scope="col" class="manage-column column-thumb" style=""><span>&nbsp;</span></th>
		<th scope="col" class="manage-column column-name" style=""><span>Nazwa</span></th>
		<th scope="col" class="manage-column column-dateCreated" style=""><span>Data dodania</span></th>
		<th scope="col" class="manage-column column-price" style=""><span>Cena sprzedaży</span></th>
		<th scope="col" class="manage-column column-allowDownload" style=""><span>Pobieranie</span></th>
		<th scope="col" class="manage-column column-action" style=""><span>&nbsp;</span></th>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<th scope="col" class="manage-column column-thumb" style=""><span>&nbsp;</span></th>
		<th scope="col" class="manage-column column-name" style=""><span>Nazwa</span></th>
		<th scope="col" class="manage-column column-dateCreated" style=""><span>Data dodania</span></th>
		<th scope="col" class="manage-column column-price" style=""><span>Cena sprzedaży</span></th>
		<th scope="col" class="manage-column column-allowDownload" style=""><span>Pobieranie</span></th>
		<th scope="col" class="manage-column column-action" style=""><span>&nbsp;</span></th>
	</tr>
	</tfoot>

	<tbody id="the-list">

	<?php if(!count($products)):?>
	<tr>
		<td colspan="100">
			<div class="error">
				Nie masz jeszcze żadnych produktów w ofercie. Aby dodać nowe produkty, skorzystaj z
				<a href="https://www.ementor.pl/panel" target="_blank">Panelu Autora</a> lub skontaktuj się z
				Obsługą Klienta
			</div>
		</td>
	</tr>

	<?php endif;?>

	<?php foreach($products as $p):?>
	<tr id="product-<?php echo $p['id'];?>" class="type-product alternate status-<?php echo $p->status ?>" valign="top">
		<td class="column-thumb"><img src="<?php echo esc_attr(Util::getProductThumbUrl($p['id'],'small')) ?>" /></td>
		<td class="column-name">
			<strong>
				<a class="row-title"><?php echo $p['name']; ?></a>
			</strong>
		</td>
		<td class="column-dateCreated"><?php echo date_i18n('j M Y',$p['dateCreated'])?></td>
		<td class="column-price"><?php echo number_format($p['price'],2,',','.'); ?>&nbsp;zł</td>
		<td class="column-allowDownload" style=""><?php echo $p['allowDownload'] ? 'dozwolone' : 'zablokowane'?></td>
		<td class="column-action">
<!--			--><?php //if($e['active']):?>
<!--			<button type="button" onclick="BMC.product.toggleActive(--><?php //echo $e['id']?><!--)" class="active-->
<!--			button-primary">-->
<!--				WYŁĄCZ-->
<!--			</button>-->
<!--			--><?php //else: ?>
<!--			<button type="button" onclick="BMC.product.toggleActive(--><?php //echo $e['id']?><!--)" class="inactive-->
<!--			button-secondary">-->
<!--				AKTYWUJ-->
<!--			</button>-->
<!--			--><?php //endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
</div>


