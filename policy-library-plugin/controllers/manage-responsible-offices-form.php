<?php
    $table_name = $wpdb->prefix . 'responsible_offices'; // do not forget about tables prefix

    $message = '';
    $notice = '';

    // this is default $item which will be used for new records
    $default = array(
        'id' => 0,
		'division_id' => '',
        'name' => ''
    );

    // here we are verifying does this request is post back and have correct nonce
    if (wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        // combine our default item with request params
        $item = shortcode_atts($default, $_REQUEST);
        // validate data, and if all ok save item to database
        // if id is zero insert otherwise update
        $item_valid = policy_library_plugin_validate_responsible_offices($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'policy_library_plugin');
                } else {
                    $notice = __('There was an error while saving item', 'policy_library_plugin');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'policy_library_plugin');
                } else {
                    $notice = __('There was an error while updating item', 'policy_library_plugin');
                }
            }
        } else {
            // if $item_valid not true it contains error message(s)
            $notice = $item_valid;
        }
    }
    else {
        // if this is not post back we load item to edit or give new one to create
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'policy_library_plugin');
            }
        }
    }

    // here we adding our custom meta box
    add_meta_box('responsible_offices_form_meta_box', 'Division data', 'policy_library_plugin_responsible_offices_form_meta_box_handler', 'responsible_offices', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Responsible office', 'policy_library_plugin')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=responsible_offices');?>"><?php _e('back to list', 'policy_library_plugin')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php /* And here we call our custom meta box */ ?>
                    <?php do_meta_boxes('responsible_offices', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'policy_library_plugin')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>

<?php


/**
 * This function renders our custom meta box
 * $item is row
 *
 * @param $item
 */
function policy_library_plugin_responsible_offices_form_meta_box_handler($item)
{
	global $wpdb;
	$divisions_data = $wpdb->get_results("select * from ".$wpdb->prefix."divisions");

    ?>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
    <tbody>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="name"><?php _e('Division', 'policy_library_plugin')?></label>
        </th>
        <td>
            <select name="division_id" id="division_id" required>
           	<?php foreach($divisions_data as $division) { ?>
            <option value="<?php echo $division->id; ?>" <?php if($division->id == $item['division_id']) { echo 'selected'; } ?>><?php echo $division->name; ?></option>
            <?php } ?>
            </select>
        </td>
    </tr>
    <tr class="form-field">
        <th valign="top" scope="row">
            <label for="name"><?php _e('Responsible office Name', 'policy_library_plugin')?></label>
        </th>
        <td>
            <input id="name" name="name" type="text" style="width: 95%" value="<?php echo esc_attr($item['name'])?>"
                   size="50" class="code" placeholder="<?php _e('Office name', 'policy_library_plugin')?>" required>
        </td>
    </tr>
    </tbody>
</table>
<?php
}

/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function policy_library_plugin_validate_responsible_offices($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Name is required', 'policy_library_plugin');
	if (empty($item['division_id'])) $messages[] = __('Division is required', 'policy_library_plugin');
  

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}
