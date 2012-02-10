<?php
use EMT\Wordpress\Util;

media_upload_header();

global $wpdb, $wp_query, $wp_locale, $type, $tab, $post_mime_types;

$post_id = isset( $_REQUEST['post_id'] )? intval( $_REQUEST['post_id'] ) : 0;

$form_class = 'media-upload-form type-form validate';

if ( get_user_setting('uploader') )
	$form_class .= ' html-uploader';
?>

<form id="filter" action="" method="get">
	<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>" />
	<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
	<input type="hidden" name="post_id" value="<?php echo (int) $post_id; ?>" />
	<input type="hidden" name="post_mime_type" value="<?php echo isset( $_GET['post_mime_type'] ) ? esc_attr( $_GET['post_mime_type'] ) : ''; ?>" />

<p id="media-search" class="search-box">
	<label class="screen-reader-text" for="media-search-input"><?php _e('Search Media');?>:</label>
	<input type="text" id="media-search-input" name="s" value="<?php echo esc_attr($search); ?>" />
	<?php submit_button( __( 'Search Media' ), 'button', '', false ); ?>
</p>


<div class="tablenav emt-media-tablenav">

<?php
$page_links = paginate_links( array(
	'base' => add_query_arg( 'wp-ementor-page', '%#%' ),
	'format' => '',
	'prev_text' => __('&laquo;'),
	'next_text' => __('&raquo;'),
	'total' => $client->getTotalCount() > 0 ? ceil($client->getTotalCount() / $limit) : 0,
	'current' => $page,
	'mid_size' => 1,
));

if ( $client->getTotalCount() > 0 && $page_links )
	echo "<div class='tablenav-pages'>$page_links</div>";
?>

<div class="alignleft actions">
	<select name="productId" id="wp-ementor-mediaTab-productId">
		<option
			ref="<?php echo remove_query_arg('productId'); ?>"
			value=""
		>Wszystkie produkty</option>
		<?php foreach($products as $p):?>
			<option
				ref="<?php echo remove_query_arg('wp-ementor-page',add_query_arg('productId', $p['id'])); ?>"
				value="<?php echo esc_attr($p['id']); ?>"
				class="emt-product emt-product-status-<?php echo $p['status']?>"
				<?php selected($p['id'], $productId) ?>
			>
				<?php echo $p['name']; ?>
			</option>
		<?php endforeach;?>
	</select>
<!--	--><?php //submit_button( __( 'Filter &#187;' ), 'secondary', 'post-query-submit', false ); ?>
</div>

</form>
</div>

<?php if(!count($media)):?>

<div class="updated message">
	Nie znaleziono żadnych mediów....
</div>
<?php endif;?>


<div id="media-items" class="emt-media-items">
	<?php
		foreach($media as $m):
			$name =  $m['name'] ? $m['name'] : "Media ".$m['id'];

	?>
		<div id="media-item-<?php echo $m['id']; ?>" class="media-item preloaded"
			data-id="<?php echo esc_attr($m['id']); ?>"
			data-name="<?php echo esc_attr($name); ?>"
			data-productId="<?php echo esc_attr($m['productId']); ?>"
			data-type="<?php echo esc_attr($m['type']); ?>"
		>
			<a class="toggle describe-toggle-on" href="#">Pokaż</a>
			<a class="toggle describe-toggle-off" href="#">Ukryj</a>
			<div class="insert-media-checkbox">
				<input type="checkbox" name="insert-media-<?php echo esc_attr($m['id']); ?>" class="insert-media" />
			</div>

			<?php if($m['type'] == 'video'):?>
			<img class="pinkynail toggle" src="<?php echo esc_attr(Util::getMediaThumbUrl($m['productId'],$m['id'],'medium'));?>" alt="">
			<?php else:?>
			<img class="pinkynail toggle" src="<?php echo esc_attr(plugins_url('img/other-media.png',EMT_ABSPLUGIN));?>" alt="">
			<?php endif;?>

			<div class="filename new">
				<span class="title">
					<?php echo $name; ?>
				</span>
				<span class="title disabled">
					<?php echo '<span class="gray">('. $m['productName'] .')</span>'; ?>
				</span>
			</div>
			<table class="slidetoggle describe startclosed" style="">
				<thead class="media-item-info" id="media-head-<?php echo $m['id']?>">
				<tr valign="top">
					<td class="thumbnail" id="thumbnail-head-<?php echo $m['id']?>">
						<p>
							<a href="#" target="_blank">
								<?php if($m['type'] == 'video'):?>
								<img class="thumbnail" src="<?php echo esc_attr(Util::getMediaThumbUrl($m['productId'],$m['id'],'medium'));?>" alt="">
								<?php else:?>
								<img class="thumbnail" src="<?php echo esc_attr(plugins_url('img/other-media.png',EMT_ABSPLUGIN));?>" alt="">
								<?php endif;?>
							</a>
						</p>
					</td>
					<td>
						<p><strong>Nazwa medium:</strong> <?php echo $m['name'] ? $m['name'] : 'Media '.$m['id'];?></p>
						<p><strong>Typ:</strong> <?php echo $m['type']?></p>
						<p><strong>Data utworzenia:</strong> <?php echo date_i18n('j M Y',$m['dateCreated'])?></p>
					</td>
				</tr>
				</thead>
				<tbody>

				<!--<tr>
					<td colspan="2" class="imgedit-response" id="imgedit-response-6"></td>
				</tr>
				<tr>
					<td style="display:none" colspan="2" class="image-editor" id="image-editor-6"></td>
				</tr>
				<tr class="post_title form-required">
					<th valign="top" scope="row" class="label"><label for="attachments[6][post_title]"><span
						class="alignleft">Tytuł</span><span class="alignright"><abbr title="required"
																					 class="required">*</abbr></span><br
						class="clear"></label></th>
					<td class="field"><input type="text" class="text" id="attachments[6][post_title]"
											 name="attachments[6][post_title]" value="2z4ckgn" aria-required="true">
					</td>
				</tr>
				<tr class="image_alt">
					<th valign="top" scope="row" class="label"><label for="attachments[6][image_alt]"><span
						class="alignleft">Tekst alternatywny</span><br class="clear"></label></th>
					<td class="field"><input type="text" class="text" id="attachments[6][image_alt]"
											 name="attachments[6][image_alt]" value="">

						<p class="help">Tekst, który może zostać wyświetlony zamiast obrazka, np. „Mona Lisa”</p></td>
				</tr>
				<tr class="post_excerpt">
					<th valign="top" scope="row" class="label"><label for="attachments[6][post_excerpt]"><span
						class="alignleft">Etykieta</span><br class="clear"></label></th>
					<td class="field"><input type="text" class="text" id="attachments[6][post_excerpt]"
											 name="attachments[6][post_excerpt]" value=""></td>
				</tr>
				<tr class="post_content">
					<th valign="top" scope="row" class="label"><label for="attachments[6][post_content]"><span
						class="alignleft">Opis</span><br class="clear"></label></th>
					<td class="field"><textarea id="attachments[6][post_content]"
												name="attachments[6][post_content]"></textarea></td>
				</tr>
				<tr class="url">
					<th valign="top" scope="row" class="label"><label for="attachments[6][url]"><span class="alignleft">Adres URL odnośnika</span><br
						class="clear"></label></th>
					<td class="field">
						<input type="text" class="text urlfield" name="attachments[6][url]"
							   value="http://acme.test/wp-content/uploads/2012/02/2z4ckgn.jpg"><br>
						<button type="button" class="button urlnone" title="">Brak</button>
						<button type="button" class="button urlfile"
								title="http://acme.test/wp-content/uploads/2012/02/2z4ckgn.jpg">Adres URL pliku
						</button>
						<button type="button" class="button urlpost" title="http://acme.test/?attachment_id=6">
							Attachment Post URL
						</button>
						<p class="help">Wprowadź adres URL odnośnika lub wybierz jeden z predefiniowanych powyżej.</p>
					</td>
				</tr>
				<tr class="align">
					<th valign="top" scope="row" class="label"><label for="attachments[6][align]"><span
						class="alignleft">Wyrównanie</span><br class="clear"></label></th>
					<td class="field"><input type="radio" name="attachments[6][align]" id="image-align-none-6"
											 value="none" checked="checked"><label for="image-align-none-6"
																				   class="align image-align-none-label">Brak</label>
						<input type="radio" name="attachments[6][align]" id="image-align-left-6" value="left"><label
							for="image-align-left-6" class="align image-align-left-label">Do lewej</label>
						<input type="radio" name="attachments[6][align]" id="image-align-center-6" value="center"><label
							for="image-align-center-6" class="align image-align-center-label">Do środka</label>
						<input type="radio" name="attachments[6][align]" id="image-align-right-6" value="right"><label
							for="image-align-right-6" class="align image-align-right-label">Do prawej</label></td>
				</tr>
				<tr class="image-size">
					<th valign="top" scope="row" class="label"><label for="attachments[6][image-size]"><span
						class="alignleft">Rozmiar</span><br class="clear"></label></th>
					<td class="field">
						<div class="image-size-item"><input type="radio" name="attachments[6][image-size]"
															id="image-size-thumbnail-6" value="thumbnail"><label
							for="image-size-thumbnail-6">miniatury</label> <label for="image-size-thumbnail-6"
																				  class="help">(150&nbsp;×&nbsp;150)</label>
						</div>
						<div class="image-size-item"><input type="radio" name="attachments[6][image-size]"
															id="image-size-medium-6" value="medium"
															checked="checked"><label
							for="image-size-medium-6">Średni</label> <label for="image-size-medium-6" class="help">(300&nbsp;×&nbsp;225)</label>
						</div>
						<div class="image-size-item"><input type="radio" disabled="disabled"
															name="attachments[6][image-size]" id="image-size-large-6"
															value="large"><label for="image-size-large-6">Duży</label>
						</div>
						<div class="image-size-item"><input type="radio" name="attachments[6][image-size]"
															id="image-size-full-6" value="full"><label
							for="image-size-full-6">Pełny rozmiar</label> <label for="image-size-full-6" class="help">(600&nbsp;×&nbsp;450)</label>
						</div>
					</td>
				</tr>-->
				<tr class="submit">
					<td></td>
					<td class="savesend">
						<input type="button" name="insert-media" id="insert-media-<?php echo $m['id']?>"
							class="button" value="<?php echo esc_attr__('Insert into Post')?>" />
					</td>
				</tr>

				</tbody>
			</table>
		</div>
	<?php endforeach; ?>
</div>

<p class="savebutton ml-submit">
	<input type="button" name="insert-media-all" id="insert-media-all"
	class="button" value="<?php echo esc_attr__('Wstaw wszystkie zaznaczone media')?>" />
</p>

<!--<p class="savebutton ml-submit">-->
<!--	--><?php //submit_button( __( 'Save all changes' ), 'button', 'save', false ); ?>
<!--</p>-->
</form>
<?php
