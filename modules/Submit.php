<?php
class WP_MADEIT_FORM_Module_Submit
{
    private $tags = array();
    private $message_fields = array('submit' => array());
    
    public function __construct() {
        $this->addTag('submit', __('Submit', 'madeit_forms'), 'tag_generator_submit', array($this, 'tag_generator_submit'), array($this, 'validation_submit'));
        $this->addHooks();
    }
    
    private function addTag($name, $title, $content, $form, $validation) {
        $this->tags[$name] = array('title' => $title, 'content' => $content, 'form' => $form, 'validation' => $validation);
    }
        
    private function addMessageField($tag, $name, $label, $value = "") {
        $this->message_fields[$tag][] = array('field' => $name, 'description' => $label, 'value' => $value);
    }
    
    public function getAction($actions) {
        $ar = array();
        foreach($this->tags as $key => $tag) {
            $ar[$key] = $tag;
            $ar[$key]['message_fields'] = isset($this->message_fields[$key]) && is_array($this->message_fields[$key]) ? $this->message_fields[$key] : array();
        }
        return array_merge($actions, $ar);
    }
    
    public function tag_generator_submit($contact_form, $args = '') {
        $args = wp_parse_args( $args, array() );
        $type = $args['id'];

        $description = __("Generate a form-tag for a submit button. For more details, see %s.", 'madeit_forms');
        $desc_link = '<a href="' . esc_url('https://www.madeit.be/wordpress/forms/docs/submit-button/') . '" target="_blank">' . __('Text Fields', 'madeit_forms') . '</a>';

        ?>
        <div class="control-box">
            <fieldset>
                <legend><?php echo sprintf(esc_html($description), $desc_link); ?></legend>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-values'); ?>"><?php echo esc_html(__('Label', 'madeit_forms')); ?></label></th>
                            <td>
                                <input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /><br />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-id'); ?>"><?php echo esc_html(__('Id attribute', 'madeit_forms')); ?></label></th>
                            <td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr($args['content'] . '-id'); ?>" /></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="<?php echo esc_attr($args['content'] . '-class'); ?>"><?php echo esc_html(__('Class attribute', 'madeit_forms')); ?></label></th>
                            <td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr($args['content'] . '-class'); ?>" /></td>
                        </tr>
                    </tbody>
                </table>
            </fieldset>
        </div>
        <div class="insert-box">
            <input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />
            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr(__('Insert Tag', 'madeit_forms')); ?>" />
            </div>
        </div>
        <?php
    }
    
    public function validation_submit($tagOptions, $value, $messages) {
        return true;
    }
    
    public function submitShortcode($atts) {
        extract(shortcode_atts(array(
            'name' => "",
            'id' => '',
            'class' => '',
            'value' => '',
        ), $atts ));
        ob_start();
        ?>
        <input type="submit" 
           <?php if($value != "") { ?> value="<?php echo esc_html($value); ?>" <?php } ?>
           <?php if($id != "") { ?> id="<?php echo esc_html($id); ?>" <?php } ?>
           <?php if($class != "") { ?> class="<?php echo esc_html($class); ?>" <?php } ?>
               >
        <?php
        $content = ob_get_clean();
        return $content;
    }

    public function addHooks() {
        add_filter('madeit_forms_modules', array($this, 'getAction'));
        
        
        add_shortcode('submit', [$this, 'submitShortcode']);
    }
}