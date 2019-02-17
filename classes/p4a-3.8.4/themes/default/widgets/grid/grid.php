<?php
/**
 * This file is part of P4A - PHP For Applications.
 *
 * P4A is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 * 
 * P4A is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with P4A.  If not, see <http://www.gnu.org/licenses/lgpl.html>.
 * 
 * To contact the authors write to:                                     <br />
 * Fabrizio Balliano <fabrizio@fabrizioballiano.it>                     <br />
 * Andrea Giardina <andrea.giardina@crealabs.it>
 *
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 * @link http://p4a.sourceforge.net
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package p4a
 */
?>

<script type="text/javascript">

function upgrade_grid(obj, set_focus) {
	set_focus = typeof(set_focus) == 'undefined' ? true : set_focus;
	
	if (obj.find('textarea').length) {
		return;
	}
	id = obj.attr('id');
	
	var value;
	var input;
	var height;
	var width;
	var a_id;
	var next_id;
	var prev_id;
	
	/* TODO: I don't use this var at the moment */
	var num_cols = <?php echo count($table_cols); ?>;
	
	value = obj.text();
	height = obj.height();
	width = obj.width();
	title = obj.attr('title');
	obj.text("");
	
	input = $("<textarea title='"+title+"' class='p4a_grid_text' id='"+id+"_text' type='text'></textarea>");				
	input.val(value);
	input.height(height-2);
	input.width(width-2);

	input.focus (function(){
		if (!$(this).parent().prevAll(".p4a_grid_td_enabled").length) {
			prev = $(this).parent().parent().prev().children(".p4a_grid_td_enabled:last");
		} else {
			prev = $(this).parent().prev(".p4a_grid_td_enabled");
		}
		
		if (!$(this).parent().nextAll(".p4a_grid_td_enabled").length) {
			next = $(this).parent().parent().next().children(".p4a_grid_td_enabled:first");
		} else {
			next = $(this).parent().next(".p4a_grid_td_enabled");
		}
		
		upgrade_grid(prev, false);
		upgrade_grid(next, false);
	});
	
	input.change(function(){
		id = $(this).attr('id');
		a_id = id.split('_');
		pk_value = a_id[1];
		field_name = $(this).attr('title');
		p4a_event_execute_ajax(a_id[0],'prechange',pk_value,field_name,input.val());
	});
	
	obj.append(input);
	obj.width(width);
	obj.height(height);

	if (set_focus) input.focus();
}

function jq(myid) { 
	return '#'+myid.replace(/:/g,"\\:").replace(/\./g,"\\.").replace(/=/g,"\\=");
}

$(document).ready(function() {
	$("td.p4a_grid_td_enabled").each(function() {
		$(this).click(function(){
			if (!$(this).find('textarea').length) {
				upgrade_grid($(this));
			} 
		});
		
	});
});

</script>
		
<table id="<?php echo $this->getId() ?>" <?php echo $this->composeStringClass() ?> <?php echo $this->composeStringProperties() ?>>
	<?php if ($this->getLabel()): ?>
	<caption><?php echo __($this->getLabel()) ?></caption>
	<?php endif; ?>

	<?php foreach ($table_cols as $col): ?>
	<col <?php echo $col['properties'] ?> />
	<?php endforeach; ?>

	<?php if (@$headers): ?>
	<thead>
		<tr>
			<?php foreach ($headers as $header): ?>
			<?php if ($header['action']): ?>
				<th>
					<?php if ($header['order'] == 'asc'): ?>
						<div style="float:right">&#x25BC;</div>
					<?php elseif ($header['order'] == 'desc'): ?>
						<div style="float:right">&#x25B2;</div>
					<?php endif; ?>
					<a href="#" <?php echo $header['action']?>><?php echo $header['value']?></a>
				</th>
			<?php else: ?>
				<th>
					<?php if ($header['order'] == 'asc'): ?>
						<div style="float:right">&#x25BC;</div>
					<?php elseif ($header['order'] == 'desc'): ?>
						<div style="float:right">&#x25B2;</div>
					<?php endif; ?>
					<?php echo $header['value']?>
				</th>
			<?php endif; ?>
			<?php endforeach; ?>
		</tr>
	</thead>
	<?php endif; ?>

	<?php if (!empty($table_rows)): ?>
	<tbody <?php echo $this->rows->composeStringProperties()?> <?php echo $this->rows->composeStringClass()?>>
		<?php $i = 0; ?>
		<?php foreach ($table_rows as $row): ?>
			<?php $i++; ?>
			<tr>
				<?php foreach ($row['cells'] as $cell): ?>
					<td title="<?php echo $cell['title'] ?>" id="<?php echo $cell['id'] ?>" class="<?php echo $cell['class'] ?> <?php echo $cell['type']?>"><?php echo $cell['value']?></td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
	<?php endif; ?>
	<?php if (isset($navigation_bar)): ?>
		<tr><th colspan='99' class="p4a_toolbar"><?php echo $navigation_bar ?></th></tr>
	<?php endif; ?>
</table>