<?php /* $Id */
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

//    This is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    This is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with FreePBX.  If not, see <http://www.gnu.org/licenses/>.
//

$ast_ge_16 = version_compare($amp_conf['ASTVERSION'], "1.6", "ge");

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';
isset($_REQUEST['number'])?$number = $_REQUEST['number']:$number='';

if($ast_ge_16) {
	isset($_REQUEST['description'])?$description = $_REQUEST['description']:$description='';
}

isset($_REQUEST['editnumber'])?$editnumber = $_REQUEST['editnumber']:$editnumber='';

$dispnum = "whitelist"; //used for switch on config.php
    
//if submitting form, update database

if(isset($_REQUEST['action'])) {
	switch ($action) {
		case "add":
			whitelist_add($_POST);
			redirect_standard();
		break;
		case "delete":
			whitelist_del($number);
			redirect_standard();
		break;
    case "edit":
      whitelist_del($editnumber);
			whitelist_add($_POST);
			redirect_standard('editnumber');
                break;
	}
}

//if submitting form, update database
if(isset($_POST['config'])) {
	// add code here to save module config
	needreload();
	redirect_standard();
	}


?>
<form autocomplete="off" name="config" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit();">
<table>


	<tr><td colspan="2"><h5><?php echo _("Whitelist Module Configuration") ?><hr></h5></td></tr>
	<tr>
		<td><?php echo _("Destination for whitelisted calls")?>:</td>
		<td><?php echo drawselects($dest1,0); ?></td>
	</tr>
	<tr>
		<td><br><?php echo _("Destination non-whitelisted calls")?>:</td>
		<td><?php echo drawselects($dest2,0); ?></td>
	</tr>
	<tr>	
		<td><br><a href="#" class="info"><?php echo _("Allow Unknown/Blocked Caller ID to pass")?>
		<span><?php echo _("Check here to allow Unknown/Blocked Caller ID")?></span></a></td>
		<td><br><input type="checkbox" name="blocked" value="1" <?php echo ($filter_blocked === true?" checked=1":"");?></td>
	</tr>
	<tr>
			<td colspan="2"><br><h6><input name="config" type="submit"  value="<?php echo _("Update Config")?>"></h6></td>
	</tr>
</form>

<form autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="add">
	<input type="hidden" name="editnumber" value="">

	<?php if($ast_ge_16) {
    	    echo "<input type=\"hidden\" name=\"editdescripton\" value=\"\">";
	    }?>
	

	<tr><td colspan="2"><h5><?php echo _("Add or replace Asterisk Phonebook entry") ?><hr></h5></td></tr>

        <tr>
    	        <td><a href="#" class="info"><?php echo _("Number/CallerID:")?>
    		<span><?php echo _("Enter the number/CallerID you want to add to the Asterisk Phonebook")?></span></a></td>
                <td><input type="text" name="number"></td>
        </tr>
        <?php if($ast_ge_16) {
    		echo "<tr>";
                echo "<td><a href=\"#\" class=\"info\">"._("Description:");
                echo "<span>"._("Enter a description for the number")."</span></a></td>";
                echo "<td><input type=\"text\" name=\"description\"></td>";
        echo "</tr>";        
	    }?>

        
	</table>
	<?php echo $module_hook->hookHtml;?>
	<table>
		<tr>
			<td colspan="2"><br><h6><input name="submit" type="submit" value="<?php echo _("Update Phonebook")?>"></h6></td>
		</tr>
	</table>
</form>
<?php
$numbers = whitelist_list();

if ($action == 'delete') 
	echo '<h3>'._("Asterisk Phonebook entry").' '.$itemid.' '._("deleted").'!</h3>';

if (is_array($numbers)) {

?>
<table cellpadding="5">
        <tr>
		<td colspan="4"><h5><?php echo _("Asterisk Phonebook entries") ?><hr></h5></td>
	</tr>

	<tr>
		
	<?php
	if($ast_ge_16) {
	    echo "<td><b>"._("Number/CallerID")."</b></td>";
	    echo "<td><b>"._("Description")."</b></td>";
		} else {
		echo "<td><b>"._("Number")."</b></td>";
		echo "<td>&nbsp;</td>";
	    }
?>		
		<td>&nbsp;</td>		
	</tr>

<?php
// Why should I specify type=setup ???
	$filter_blocked = false;
	foreach ($numbers as $num)	{
		
		
    if($ast_ge_16) {
			print('<tr>');
			printf('<td>%s</td>', $num['number']);
 			printf('<td>%s</td>', $num['description']);
			printf('<td><a href="%s?type=setup&display=%s&number=%s&action=delete">%s</a></td>', 
			 $_SERVER['PHP_SELF'], urlencode($dispnum), urlencode($num['number']), _("Delete"));
			printf('<td><a href="#" onClick="theForm.number.value = \'%s\'; 
				theForm.editnumber.value = \'%s\' ;
				theForm.description.value = \'%s\'; 
				theForm.editdescription.value = \'%s\' ; 
				theForm.action.value = \'edit\' ; ">%s</a></td>',$num['number'], $num['number'], $num['description'], $num['description'], _("Edit"));
			print('</tr>');
			
			} else {
			print('<tr>');
			printf('<td>%s</td>', $num);
			printf('<td><a href="%s?type=setup&display=%s&number=%s&action=delete">%s</a></td>', 
			 $_SERVER['PHP_SELF'], urlencode($dispnum), urlencode($num), _("Delete"));
			printf('<td><a href="#" onClick="theForm.number.value = \'%s\'; theForm.editnumber.value = \'%s\' ; theForm.action.value = \'edit\' ; ">%s</a></td>',$num, $num, _("Edit"));
			print('</tr>');
			}
		
	}
	print('</table>');
}
?>

<!--TODO: This should be jquery!-->
<script language="javascript">
	var theForm = document.edit;
	theForm.number.focus();

	function isDialDigitsPlus(s)
	{
		var i;

		if (isEmpty(s)) {
			return false;
		}

		for (i = 0; i < s.length; i++) {
			var c = s.charAt(i);

			if (!isCallerIDChar(c) && (c != "+")) return false;
		}
		return true;
	}


	function edit_onsubmit() {
		defaultEmptyOK = false;
	        if (theForm.number.value && !isDialDigitsPlus(theForm.number.value))
	                return warnInvalid(theForm.number, "Please enter a valid Number");
		return true;
	}
</script>