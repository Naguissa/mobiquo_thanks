<?php
global $auth;
define('MOBIQUO_CALL_HANDLE_ERROR', true);


if(MbqMain::$cmd != 'report_pm')
{
    include_once('mcp_clone.' . $phpEx);
}
class fake_template extends template
{
    public $pagination;
    public function getTemplateVars()
    {
        $vars = $this->_tpldata;
        $vars = $vars['.'][0];
        return $vars;
    }
    public function getTemplateVar($varname)
    {
        $var = $this->_tpldata;

        if(isset($var['.'][0][$varname]))
        {
            $var = $var['.'][0][$varname];
        }
        else
        {
            $var = $this->_rootref;
            if(isset($var[$varname]))
            {
                $var = $var[$varname];
            }
            else
            {
                $var = null;
            }
        }
        return $var;
    }
    public function getTemplateBlockVar($varname)
    {
        $var = $this->_tpldata;
        if(isset($var[$varname]))
        {
            $var = $var[$varname];
        }
        else
        {
            $var = null;
        }
        return $var;
    }
    public function getContext()
    {
        return $this->context;
    }
    public function __construct()
	{
        global $request, $user, $phpbb_container, $phpbb_dispatcher, $helper;
       // $this->pagination = new fake_pagination($this, $user, $helper, $phpbb_dispatcher);
        //$phpbb_container->set('pagination', $this->pagination);
        //$this->context = new \phpbb\template\context();
        $_POST['submit'] = true;
        ////setup variables to override confirm box
        $request->overwrite('confirm', $user->lang['YES'], \phpbb\request\request_interface::POST);
        $request->overwrite('confirm_uid', $user->data['user_id'], \phpbb\request\request_interface::REQUEST);
        $request->overwrite('sess', $user->session_id, \phpbb\request\request_interface::REQUEST);
        $user->data['user_last_confirm_key'] = $user->session_id;
        $request->overwrite('confirm_key', $user->data['user_last_confirm_key'], \phpbb\request\request_interface::REQUEST);

    }

    public function assign_var($varname, $varval)
    {
        return parent::assign_var($varname, $varval);
    }
    ///**
    // * Clear the cache
    // *
    // * @return \phpbb\template\template
    // */
    //public function clear_cache(){}

    ///**
    // * Sets the template filenames for handles.
    // *
    // * @param array $filename_array Should be a hash of handle => filename pairs.
    // * @return \phpbb\template\template $this
    // */
    //public function set_filenames(array $filename_array){}

    ///**
    // * Get the style tree of the style preferred by the current user
    // *
    // * @return array Style tree, most specific first
    // */
    //public function get_user_style(){}

    ///**
    // * Set style location based on (current) user's chosen style.
    // *
    // * @param array $style_directories The directories to add style paths for
    // * 	E.g. array('ext/foo/bar/styles', 'styles')
    // * 	Default: array('styles') (phpBB's style directory)
    // * @return \phpbb\template\template $this
    // */
    //public function set_style($style_directories = array('styles')){}

    ///**
    // * Set custom style location (able to use directory outside of phpBB).
    // *
    // * Note: Templates are still compiled to phpBB's cache directory.
    // *
    // * @param string|array $names Array of names or string of name of template(s) in inheritance tree order, used by extensions.
    // * @param string|array or string $paths Array of style paths, relative to current root directory
    // * @return \phpbb\template\template $this
    // */
    //public function set_custom_style($names, $paths){}

    ///**
    // * Clears all variables and blocks assigned to this template.
    // *
    // * @return \phpbb\template\template $this
    // */
    //public function destroy(){}

    ///**
    // * Reset/empty complete block
    // *
    // * @param string $blockname Name of block to destroy
    // * @return \phpbb\template\template $this
    // */
    //public function destroy_block_vars($blockname){}

    ///**
    // * Display a template for provided handle.
    // *
    // * The template will be loaded and compiled, if necessary, first.
    // *
    // * This function calls hooks.
    // *
    // * @param string $handle Handle to display
    // * @return \phpbb\template\template $this
    // */
    //public function display($handle){}

    ///**
    // * Display the handle and assign the output to a template variable
    // * or return the compiled result.
    // *
    // * @param string $handle Handle to operate on
    // * @param string $template_var Template variable to assign compiled handle to
    // * @param bool $return_content If true return compiled handle, otherwise assign to $template_var
    // * @return \phpbb\template\template|string if $return_content is true return string of the compiled handle, otherwise return $this
    // */
    //public function assign_display($handle, $template_var = '', $return_content = true){}





    ///**
    // * Get path to template for handle (required for BBCode parser)
    // *
    // * @param string $handle Handle to retrieve the source file
    // * @return string
    // */
    //public function get_source_file_for_handle($handle){}
}

//class fake_pagination//extends \phpbb\pagination
//{
//   public $total;
//    /**
//     * Constructor
//     *
//     * @param	\phpbb\template\template			$template
//     * @param	\phpbb\user							$user
//     * @param	\phpbb\controller\helper			$helper
//     * @param	\phpbb\event\dispatcher_interface	$phpbb_dispatcher
//     */
//    public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\controller\helper $helper, \phpbb\event\dispatcher_interface $phpbb_dispatcher)
//    {
//        $this->template = $template;
//        $this->user = $user;
//        $this->helper = $helper;
//        $this->phpbb_dispatcher = $phpbb_dispatcher;
//    }

//    public function generate_template_pagination($base_url, $block_var_name, $start_name, $num_items, $per_page, $start = 1, $reverse_count = false, $ignore_on_page = false)
//    {
//        $this->total = $num_items;
//    }
//}
