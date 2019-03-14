<?php 

	function as_navigation($request, $html = '')
	{
		$userid = isset( $_SESSION['loggedin_user'] ) ? $_SESSION['loggedin_user'] : "";
		$level = isset( $_SESSION['loggedin_level'] ) ? $_SESSION['loggedin_level'] : "";
		$navigation = array();
		if ( $userid ) {			
			$navigation['bookings'] = array('label' => 'Bookings', 'url' => 'index.php?open=booking_all');
			$navigation['results'] = array('label' => 'Results', 'url' => 'index.php?open=result_all');
			$navigation['signout'] = array('label' => 'Sign Out?', 'url' => 'index.php?open=signout');
		} else {
			$navigation['signin'] = array('label' => 'Sign In', 'url' => 'index.php?open=signin');
			$navigation['register'] = array('label' => 'SignUp', 'url' => 'index.php?open=register');
		}			
		if (isset($navigation[$request])) $navigation[$request]['selected']=true;
		foreach ($navigation as $k => $a){
			if ( $k != 'home'){
				$html .= '<li><a '.(($request==$k) ? 'class="current" ': '').'href="'.$a['url'].'">'.$a['label'].'</a></li>'."\n\t\t";
			} else {
				$html .= '<li><a '.(empty($request) ? 'class="current" ': '').'href=".">Home</a></li>'."\n\t\t";
			}
		}
		return $html;
	}
	 
	include TEMPLATE . "header.php";
	$page = $content['page'];
?>
<div id="tooplate_content">   	
	<h2>
		<?php echo (isset($content['title']) ? $content['title'] : '') ?>
		<?php echo (isset($content['link']) ? $content['link'] : '') ?>
	</h2>
		<?php switch ($page['type']){
			case 'form':
			?>
		<div id="general_form"> 
					<form action="<?php echo $page['action']?>" method="post">      
				<?php foreach($page['fields'] as $name => $field) { ?>
		<label class="label" for="<?php echo $name ?>"><?php echo $field['label'] ?></label>
				<?php 
					switch ($field['type']) {
						case 'radio':
							if (isset($field['options'])) {
								foreach ($field['options'] as $option) { ?>
		<label><input type="radio" <?php echo 'name="'.$name.'" value="'.@$option['value'].'" '. ($field['value'] == $option['value'] ? 'checked ' : '') ?>  <?php echo @$field['tags'] ?>/> <?php echo @$option['name'] ?> </label>
				<?php } ?>
		<?php }
							break;
						case 'select':
							if (isset($field['options'])) { ?>
						<select class="input_field" name="<?php echo $name  ?>" >
							<?php foreach ($field['options'] as $key => $value ) { ?>
					<option value="<?php echo $key ?>"><?php echo $value ?></option>
				<?php } ?>
				</select>
		<?php }
						break;
					case 'textarea': ?>
					<textarea id="text" name="<?php echo $name  ?>" rows="<?php echo $field['rows'] ?>" cols="<?php echo @$field['cols'] ?>" class="input_field"><?php echo @$field['value'] ?></textarea>
				<?php 
						break;
						
					default: ?>
		<input type="<?php echo $field['type'] ?>" name="<?php echo $name  ?>" value="<?php echo @$field['value'] ?>" autocomplete="off" class="input_field" <?php echo @$field['tags'] ?>/>
						<?php } ?><div class="cleaner h10"></div>
						
				<?php } ?>
		<?php 
				if (isset($page['hidden']))
					foreach ($page['hidden'] as $name => $hidden)
						echo '<input type="hidden" name="'.$name.'" value="'.$hidden.'" />';
							?>	
						<?php 
					foreach ($page['buttons'] as $name => $button)
						echo '<input type="submit" name="'.$name.'" value="'.$button['label'].'" class="submit_btn float_l"'.@$button['tags'].'/>'; ?>
			
					</form>
				</div>
		<?php break;
			case 'table': ?>
				<table class="tt_tb">
					<thead>
						<tr>
					<?php foreach($page['headers'] as $header) { ?>
				<th><?php echo $header ?></th>
					<?php } ?>
						</tr>
					</thead>
					<tbody>
					<?php foreach($page['items'] as $rid => $trow) { ?>
						<tr onclick="location='index.php?<?php echo $page['onclick'].$rid ?>'">
						<?php foreach($trow as $tdata) { ?>
							<td valign="top"><?php echo $tdata ?></td>
						<?php } ?>
						</tr>
					<?php } ?>
					</tbody>
				</table>			
		<?php break;
			case 'examine': ?>
				<hr>
		<div id="general_form"> 
				<form action="<?php echo $page['action']?>" method="post">  
				<?php 
				if (isset($page['quiz'])) { ?>
					<h3><?php echo $page['quiz'] ?></h3>					
		<?php }
				if (isset($page['answers'])) {
					$i = 1;
					foreach($page['answers'] as $answer) { ?>
					<p style="font-size: 18px;width:100%;"><label><input name="set_answer" type="radio" value="<?php echo $i ?>" required ><?php echo $answer ?></label></p>
			<?php  		$i++;
					}
				} ?>
				<?php 
				if (isset($page['hidden']))
					foreach ($page['hidden'] as $name => $hidden)
						echo '<input type="hidden" name="'.$name.'" value="'.$hidden.'" />';
							?>	
						<?php 
					foreach ($page['buttons'] as $name => $button)
						echo '<input type="submit" name="'.$name.'" value="'.$button['label'].'" class="submit_btn float_l"'.@$button['tags'].'/>'; ?>
			
					</form>
				</div>
			<?php				
			  break;
			case 'viewer': ?>
				<?php 
				if (isset($page['items'])) {
					foreach($page['items'] as $it => $item) { ?>
					<h3><?php echo $it ?> <pre><?php echo $item ?></pre></h3>
				<?php } 
				}  
				if (isset($page['subitems'])) { ?>
				<table style="font-size: 20px;">
				<?php foreach($page['subitems'] as $sit => $sitem) { ?>
					<tr><td><b><?php echo $sit ?></b></td><td> : </td> <td><?php echo $sitem ?></td></tr>
				<?php } ?>
				</table>
			<?php }  
				if (isset($page['lists'])) { ?>
				<hr>
				<h2>
					<?php echo (isset($page['lists']['title']) ? $page['lists']['title'] : '') ?>
					<?php echo (isset($page['lists']['link']) ? $page['lists']['link'] : '') ?>
				</h2>
				<table class="tt_tb">
					<thead>
						<tr>
					<?php foreach($page['lists']['headers'] as $header) { ?>
				<th><?php echo $header ?></th>
					<?php } ?>
						</tr>
					</thead>
					<tbody>
					<?php foreach($page['lists']['items'] as $rid => $trow) { ?>
						<tr>
						<?php foreach($trow as $tdata) { ?>
							<td valign="top"><?php echo $tdata ?></td>
						<?php } ?>
						</tr>
					<?php } ?>
					</tbody>
				</table>	
			<?php }
				if (isset($page['fields'])) { ?>
				<hr><h3><?php echo $page['formname'] ?></h3>
			<div id="general_form"> 
					<form action="<?php echo $page['action']?>" method="post">      
				<?php foreach($page['fields'] as $name => $field) { ?>
		<label class="label" for="<?php echo $name ?>"><?php echo $field['label'] ?></label>
				<?php 
					switch ($field['type']) {
						case 'radio':
							if (isset($field['options'])) {
								foreach ($field['options'] as $option) { ?>
		<label><input type="radio" <?php echo 'name="'.$name.'" value="'.@$option['value'].'" '. ($field['value'] == $option['value'] ? 'checked ' : '') ?>  <?php echo @$field['tags'] ?>/> <?php echo @$option['name'] ?> </label>
				<?php } ?>
		<?php }
							break;
					case 'textarea': ?>
					<textarea name="<?php echo $name  ?>" id="<?php echo $name  ?>" class="input_field"><?php echo @$field['value'] ?></textarea>
				<?php 
						break;
						case 'select':
							if (isset($field['options'])) { ?>
						<select class="input_field" name="<?php echo $name  ?>" >
							<?php foreach ($field['options'] as $key => $value ) { ?>
					<option value="<?php echo $key ?>"><?php echo $value ?></option>
				<?php } ?>
				</select>
		<?php }
					break;
					case 'textarea': ?>
					<textarea id="text" name="<?php echo $name  ?>" rows="<?php echo $field['rows'] ?>" cols="<?php echo @$field['cols'] ?>" class="input_field"><?php echo @$field['value'] ?></textarea>
				<?php 
						default: ?>
		<input type="<?php echo $field['type'] ?>" name="<?php echo $name  ?>" id="<?php echo $name  ?>" value="<?php echo @$field['value'] ?>" class="input_field" <?php echo @$field['tags'] ?> autocomplete="off"/>
						<?php } ?><div class="cleaner h10"></div>
				<?php } ?>
		<?php 
				if (isset($page['hidden']))
					foreach ($page['hidden'] as $name => $hidden)
						echo '<input type="hidden" name="'.$name.'" value="'.$hidden.'" />';
							?>	
						<?php 
					foreach ($page['buttons'] as $name => $button)
						echo '<input type="submit" name="'.$name.'" value="'.$button['label'].'" class="submit_btn float_l"'.@$button['tags'].'/>'; ?>
			
					</form>
				</div> 
			</div>
			<?php } ?>
		<?php default: ?>
		
		<?php } ?>
	</div>
<?php include TEMPLATE . "footer.php" ?>