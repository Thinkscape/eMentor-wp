<?php

use EMT\Wordpress\Util;

$updateUrl = plugins_url( 'update.php', EMT_ABSPLUGIN);
?>
<script type="text/javascript">window.emt_product_UpdateUrl = '<?php echo $updateUrl ?>';</script>
<div class="wrap">

	<div id="icon-ementor" class="icon32"><br /></div>
	<h2>Biblioteka mediów eMentor</h2>
<!--	<div class="tablenav top">-->
<!--		<input type="text" id="passName" placeholder="Imię i nazwisko klienta" />-->
<!--		<button type="button" id="newPass" class="button-secondary">-->
<!--			DODAJ OSOBĘ-->
<!--		</button>-->
<!--	</div>-->


	<table class="wp-list-table widefat fixed emt-media" cellspacing="0">
	<thead>
	<tr>
		<th scope="col" class="manage-column column-thumb" style=""><span>&nbsp;</span></th>
		<th scope="col" class="manage-column column-name" style=""><span>Nazwa</span></th>
		<th scope="col" class="manage-column column-product" style=""><span>Produkt</span></th>
		<th scope="col" class="manage-column column-dateCreated" style=""><span>Data dodania</span></th>
		<th scope="col" class="manage-column column-length" style=""><span>Długość</span></th>
		<th scope="col" class="manage-column column-isPreview" style=""><span>Darmowy?</span></th>
		<th scope="col" class="manage-column column-action" style=""><span>&nbsp;</span></th>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<th scope="col" class="manage-column column-thumb" style=""><span>&nbsp;</span></th>
		<th scope="col" class="manage-column column-name" style=""><span>Nazwa</span></th>
		<th scope="col" class="manage-column column-product" style=""><span>Produkt</span></th>
		<th scope="col" class="manage-column column-dateCreated" style=""><span>Data dodania</span></th>
		<th scope="col" class="manage-column column-length" style=""><span>Długość</span></th>
		<th scope="col" class="manage-column column-isPreview" style=""><span>Darmowy?</span></th>
		<th scope="col" class="manage-column column-action" style=""><span>&nbsp;</span></th>
	</tr>
	</tfoot>

	<tbody id="the-list">

	<?php if(!count($media)):?>
	<tr>
		<td colspan="100">
			<div class="error">
				Nie masz jeszcze żadnych produktów w ofercie. Aby dodać nowe produkty, skorzystaj z
				<a href="https://www.ementor.pl/panel" target="_blank">Panelu Autora</a> lub skontaktuj się z
				Obsługą Klienta
			</div>
		</td>
	</tr>

	<?php endif; ?>

	<?php foreach($media as $m):?>
	<tr id="product-<?php echo $m['id'];?>" class="type-media alternate status-<?php echo $m->status ?>" valign="top">
		<td class="column-thumb">
			<?php if($m['type'] == 'video'):?>
			<img src="<?php echo esc_attr(Util::getMediaThumbUrl($m['productId'],$m['id'])) ?>" />
			<?php else: ?>
			<img src="<?php echo esc_attr(plugins_url('img/other-media.png',EMT_ABSPLUGIN)) ?>" />
			<?php endif; ?>
		</td>
		<td class="column-name">
			<strong>
				<a class="row-title">
					<?php
						if($m['name']){
							echo $m['name'];
						}else{
							echo "Media ".$m['id'];
						}
					?>
				</a>
			</strong>
		</td>
		<td class="column-product"><?php echo $m['productName']; ?></td>
		<td class="column-dateCreated"><?php echo date_i18n('j M Y',$m['dateCreated'])?></td>
		<td class="column-length">
			<?php if($m['type'] == 'video'):?>
				<?php echo Util::getDottedTime($m['rawLength']); ?>
			<?php else:?>
				<?php echo Util::getDottedTime($m['rawSize']); ?>
			<?php endif; ?>
		</td>
		<td class="column-isPreview" style=""><?php echo $m['isPreview'] ? 'darmowy' : 'płatny'?></td>
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


