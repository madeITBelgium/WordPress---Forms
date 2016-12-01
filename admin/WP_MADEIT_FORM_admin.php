<?php
class WP_MADEIT_FORM_admin {
    private $db;
    private $cycles;
    private $tags = array();
    private $actions = array();
    private $messages = array();
    public function __construct() {
        $this->db = \WeDevs\ORM\Eloquent\Database::instance();
        
        $this->messages = array(
            array('field' => 'success',             'description' => __("Form successfully completed", 'forms-by-made-it'),                     'value' => __('Thank you for your message. It has been sent.', 'forms-by-made-it')),
            array('field' => 'failed',              'description' => __("Form failed to complete", 'forms-by-made-it'),                         'value' => __('There was an error trying to send your message. Please try again later.', 'forms-by-made-it')),
            array('field' => 'validation_error',    'description' => __("Validation errors occurred", 'forms-by-made-it'),                      'value' => __('One or more fields have an error. Please check and try again.', 'forms-by-made-it')),
            array('field' => 'spam',                'description' => __("Submission was referred to as spam", 'forms-by-made-it'),              'value' => __('There was an error trying to send your message. Please try again later.', 'forms-by-made-it')),
            array('field' => 'accept_terms',        'description' => __("There are terms that the sender must accept", 'forms-by-made-it'),     'value' => __('You must accept the terms and conditions before sending your message.', 'forms-by-made-it')),
            array('field' => 'invalid_required',    'description' => __("There is a field that the sender must fill in", 'forms-by-made-it'),   'value' => __('The field is required.', 'forms-by-made-it')),
            array('field' => 'invalid_too_long',    'description' => __("There is a field with input that is longer than the maximum allowed length", 'forms-by-made-it'), 'value' => __('The field is too long.', 'forms-by-made-it')),
            array('field' => 'invalid_too_short',   'description' => __("There is a field with input that is shorter than the minimum allowed length", 'forms-by-made-it'), 'value' => __('The field is too short.', 'forms-by-made-it')),
            //array('field' => '', 'description' => __("", 'forms-by-made-it'), 'value' => __('', 'forms-by-made-it')),
        );
    }
    
    public function initMenu() {
        global $_wp_last_object_menu;
        $_wp_last_object_menu++;
        add_menu_page(__('Forms', 'forms-by-made-it'), __('Forms', 'forms-by-made-it'), 'manage_options', 'madeit_forms', array($this, 'show_all'), 'dashicons-email', $_wp_last_object_menu);
        add_submenu_page('madeit_forms', __('Made I.T. Forms', 'forms-by-made-it'), __('Forms', 'forms-by-made-it'), 'manage_options', 'madeit_forms', array($this, 'show_all'));
        add_submenu_page('madeit_forms', __('Made I.T. Forms - New', 'forms-by-made-it'), __('Add new', 'forms-by-made-it'), 'manage_options', 'madeit_form', array($this, 'new_form'));
        
        $new = "";
        $count = $this->db->table('madeit_form_inputs')->where('read', 0)->count();
        if($count > 0) {
            $new = "<span class='update-plugins' title='" . __('Unread form submits', 'forms-by-made-it') . "'><span class='update-count'>" . number_format_i18n($count) . "</span></span>";
        }
        add_submenu_page('madeit_forms', __('Made I.T. Forms - Inputs', 'forms-by-made-it'), __('Submitted forms ' . $new, 'forms-by-made-it'), 'manage_options', 'madeit_form_input', array($this, 'all_inputs'));
    }
    
    public function initStyle() {
        wp_register_style('madeit-form-admin-style', MADEIT_FORM_URL . '/admin/css/style.css', array(), null);
        wp_enqueue_style('madeit-form-admin-style');
        
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');

	    wp_enqueue_script( 'madeit-form-script', MADEIT_FORM_URL . '/admin/js/script.js', array('jquery'), 1, true);
        //wp_enqueue_style('jquery-ui-smoothness',  MADEIT_FORM_URL . '/admin/css/smoothness/jquery-ui.min.css', array(), '1.10.3', 'screen');
    }
    
    public function addAction($id, $value) {
        $this->actions[$id] = $value;
        if(count($value['message_fields']) > 0) {
            $this->messages = array_merge($this->messages, $value['message_fields']);
        }
    }
    
    public function addModule($id, $value) {
        $this->tags[$id] = $value;
        if(count($value['message_fields']) > 0) {
            $this->messages = array_merge($this->messages, $value['message_fields']);
        }
    }
    
    public function initAdmin() {
		
    }
    
    public function show_all() {
        echo '<div class="wrap">';
        if(!isset($_GET['action']) || $_GET['action'] != "edit") {
            ?>
            <h1><?php echo __('Forms', 'forms-by-made-it'); ?><a href="admin.php?page=madeit_form" class="add-new-h2"><?php echo __('Add new', 'forms-by-made-it'); ?></a></h1>
            <?php
        }
        if(isset($_GET['action']) && $_GET['action'] == "delete") {
            $this->db->table('madeit_forms')->where('id', $_GET['id'])->delete();
            ?>
            <div class="updated"><p><strong><?php echo __('The form is deleted.', 'forms-by-made-it'); ?></strong></p></div>
            <?php
        }
        if(isset($_GET['action']) && $_GET['action'] == "edit") {
            if(isset($_POST['add_new']) && $_POST['add_new'] == "Y") {
                $this->post_edit_form();
            }
            $f = $this->db->table('madeit_forms')->where('id', $_GET['id'])->first();
            if(!isset($f->id)) {
                ?>
                <div class="error"><p><strong><?php echo __('The form isn\'t available', 'forms-by-made-it'); ?></strong></p></div>
                <?php
            } else {
                $form = array('id' => $f->id, 'title' => $f->title, 'form' => $f->form, 'actions' => json_decode($f->actions, true), 'messages' => json_decode($f->messages, true));
                include(MADEIT_FORM_DIR . '/admin/forms/form.php');
            }
        }
        else {
            require_once( MADEIT_FORM_DIR . '/admin/FormListTable.php');
            $list = new FormListTable();
            $list->prepare_items();
            $list->display();
        }
        echo "</div>";
    }
    
    public function new_form() {
        $from = "<p>Your name:</p>
[text name=\"your-name\"]
<p>Your email:</p>
[email name=\"your-email\"]
[submit value=\"Send\"]";
        if(isset($_POST['add_new'])) {
            $form = $this->post_form();
        } else {
            $form = array('id' => 0, 'title' => '', 'form' => $from, 'actions' => array(), 'messages' => array());
        }
        include_once MADEIT_FORM_ADMIN . '/forms/form.php';
    }
    
    public function post_form() {
        $form = array('id' => $_POST['form_id'], 'title' => $_POST['title'], 'form' => $_POST['form'], 'actions' => array(), 'messages' => array());
        $error = false;
        $error_msg = "";
        
        //actions
        $countActions = 0;
        foreach($_POST as $k => $v) {
            if(substr($k, 0, strlen("action_panel_")) == "action_panel_" && is_numeric($v) && $v > $countActions) {
                $countActions = $v;
            }
        }
        
        $j = 1;
        for($i = 1; $i <= $countActions; $i++) {
            $id = $_POST['action_type_' . $i];
            if(isset($this->actions[$id])) {
                $action = $this->actions[$id];
                $form['actions'][$j] = array('_id' => $id);
                foreach($action['action_fields'] as $name => $info) {
                    $form['actions'][$j][$name] = isset($_POST['action_' . $id . '_' . $name . '_' . $i]) ? $_POST['action_' . $id . '_' . $name . '_' . $i] : "";
                }
            }
            $j++;
        }
        
        foreach($_POST as $k => $v) {
            if(substr($k, 0, strlen("messages_")) == "messages_") {
                $form['messages'][substr($k, strlen("messages_"))] = $v;
            }
        }
        
        if(!$error) {
            $this->db->table('madeit_forms')->insert(array( 
                    'title' => $form['title'],
                    'form' => $form['form'], 
                    'actions' => json_encode($form['actions']),
                    'messages' => json_encode($form['messages']),
                    'create_time' => date('Y-m-d H:i:s')
                )
            );
            $f = $this->db->table('madeit_forms')->where('title', $form['title'])->orderBy('id', 'desc')->first();
            if(!isset($f->id)) {
                ?>
                <div class="error"><p><strong><?php echo __('The form doesn\'t exist', 'forms-by-made-it'); ?></strong></p></div>
                <?php
            } else {
                $form = array('id' => $f->id, 'title' => $f->title, 'form' => $f->form, 'actions' => json_decode($f->actions, true), 'messages' => json_decode($f->messages, true));
            }
            ?>
            <div class="updated"><p><strong><?php echo __('The form is successfully saved.', 'forms-by-made-it'); ?></strong></p></div>
            <?php
        } else {
            ?>
            <div class="error"><p><strong><?php echo __($error, 'forms-by-made-it'); ?></strong></p></div>
            <?php
        }
        return $form;
    }
        
    public function post_edit_form() {
        $form = array('id' => $_POST['form_id'], 'title' => $_POST['title'], 'form' => $_POST['form'], 'actions' => array(), 'messages' => array());
        $error = false;
        $error_msg = "";
        
        //actions
        $countActions = 0;
        foreach($_POST as $k => $v) {
            if(substr($k, 0, strlen("action_panel_")) == "action_panel_" && is_numeric($v) && $v > $countActions) {
                $countActions = $v;
            }
        }
        
        $j = 1;
        for($i = 1; $i <= $countActions; $i++) {
            $id = $_POST['action_type_' . $i];
            if(isset($this->actions[$id])) {
                $action = $this->actions[$id];
                $form['actions'][$j] = array('_id' => $id);
                foreach($action['action_fields'] as $name => $info) {
                    $form['actions'][$j][$name] = isset($_POST['action_' . $id . '_' . $name . '_' . $i]) ? $_POST['action_' . $id . '_' . $name . '_' . $i] : "";
                }
            }
            $j++;
        }
        
        foreach($_POST as $k => $v) {
            if(substr($k, 0, strlen("messages_")) == "messages_") {
                $form['messages'][substr($k, strlen("messages_"))] = $v;
            }
        }

        if($error) {
            ?>
            <div class="error"><p><strong><?php echo __($error_msg, 'forms-by-made-it'); ?></strong></p></div>
            <?php
        } else {
            $this->db->table('madeit_forms')->where('id', $_GET['id'])->update(
                array(
                    'title' => $form['title'],
                    'form' => $form['form'], 
                    'actions' => json_encode($form['actions']),
                    'messages' => json_encode($form['messages']),
                )
            );
            ?>
            <div class="updated"><p><strong><?php echo __('The form is successfully saved.', 'forms-by-made-it'); ?></strong></p></div>
            <?php
        }
    }
    
    
    public function all_inputs() {
        echo '<div class="wrap">';
        if(!isset($_GET['action']) || $_GET['action'] != "edit") {
            ?>
            <h1><?php echo __('Submitted forms', 'forms-by-made-it'); ?></h1>
            <?php
        }
        if(isset($_GET['action']) && $_GET['action'] == "delete") {
            $this->db->table('madeit_form_inputs')->where('id', $_GET['id'])->delete();
            ?>
            <div class="updated"><p><strong><?php echo __('The submitted data is deleted.', 'forms-by-made-it'); ?></strong></p></div>
            <?php
        }
        if(isset($_GET['action']) && $_GET['action'] == "show") {
            $f = $this->db->table('madeit_form_inputs')->where('id', $_GET['id'])->first();
            $form = $this->db->table('madeit_forms')->where('id', $f->form_id)->first();
            $this->db->table('madeit_form_inputs')->where('id', $_GET['id'])->update(array('read' => 1));
            if(!isset($f->id)) {
                ?>
                <div class="error"><p><strong><?php echo __('The data isn\'t available', 'forms-by-made-it'); ?></strong></p></div>
                <?php
            } else {
                include(MADEIT_FORM_DIR . '/admin/forms/submitted.php');
            }
        }
        else {
            require_once( MADEIT_FORM_DIR . '/admin/InputListTable.php');
            $list = new InputListTable();
            $list->prepare_items();
            $list->display();
        }
        echo "</div>";
    }
    
    public function getTags($form) {
        $tags = array();
        foreach(explode("[", $form) as $v) {
            $v = trim($v);
            if(strlen($v) > 0) {
                $posName = strpos($v, 'name="');
                if($posName !== false) {
                    $v = substr($v, $posName + 6);
                    $v = substr($v, 0, strpos($v, '"'));
                    $tags[] = $v;
                }
            }
        }
        return $tags;
    }
    
    public function checkFormActions($id) {
        $res = 0;
        $errors = array();
        
        $form = $this->db->table('madeit_forms')->where('id', $id)->first();
        if(isset($form->id)) {
            $formValue = $form->form;
            $formValue = str_replace('\"', '"', $formValue);
            if(isset($form->id)) {
                $tags = $this->getTags($formValue);
                $t = array();
                foreach($tags as $a) {
                    $t[$a] = "a";
                }

                //execute actions
                if(isset($form->actions) && count($form->actions) > 0) {
                    $formActions = json_decode($form->actions, true);
                    foreach($formActions as $actID => $actionInfo) {
                        $action = $this->actions[$actionInfo['_id']];

                        $data = array();
                        foreach($action['action_fields'] as $name => $info) {
                            $inputValue = isset($actionInfo[$name]) ? $actionInfo[$name] : $info['value'];
                            $data[$name] = $this->changeInputTag($t, $inputValue);
                        }

                        foreach($data as $key => $val) {
                            $pos = strpos($val, "[");
                            if($pos !== false) {
                                $posN = strpos($val, "]", $pos);
                                $space = strpos($val, " ", $pos);
                                if($posN !== false) {
                                    $res++;
                                    $errors[] = $key;
                                }
                            }
                        }
                    }
                }
            }
        }
        //print_r($errors);
        return $res;
    }
    
    private function changeInputTag($tags, $value) {
        foreach($tags as $k => $v) {
            $value = str_replace("[" . $k . "]", $v, $value);
        }
        return $value;
    }
    
    public function init() {
        $actions = apply_filters('madeit_forms_actions', array());
        foreach($actions as $id => $value) {
            $this->addAction($id, $value);
        }
        
        $modules = apply_filters('madeit_forms_modules', array());
        foreach($modules as $id => $value) {
            $this->addModule($id, $value);
        }
    }
	
    public function addHooks() {
        add_action('admin_init', array($this, 'initAdmin'));
        add_action('admin_menu', array($this, 'initMenu'));
        add_action('admin_enqueue_scripts', array($this, 'initStyle'));
        
        add_action('init', array($this, 'init'));
    }
}