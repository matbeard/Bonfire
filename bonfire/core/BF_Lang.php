<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class BF_Lang extends CI_Lang {

    /**
     * @var String The fallback language used for un-translated lines.
     * If you change this, you should ensure that all language files have been
     * translated to the language indicated by the new value.
     */
    protected $fallback = 'english';

    /**
     * Load a language file
     *
     * Bonfire modifies this to attempt to find language files within modules, also.
     *
     * @access  public
     * @param   mixed   the name of the language file to be loaded. Can be an array
     * @param   string  the language (english, etc.)
     * @param   bool    return loaded array of translations
     * @param   bool    add suffix to $langfile
     * @param   string  alternative path to look for language file
     *
     ************
     * The $module parameter has been deprecated (since 0.7.1)
     * @param   string  the name of the module in which the language file may be located
     *
     * @return  mixed
     */
    function load($langfile = '', $idiom = '', $return = false, $add_suffix = true, $alt_path = '', $module='')
    {
        $orig_langfile = $langfile;
		if (is_array($langfile)) {
			foreach($langfile as $_lang) $this->load($_lang);
			return $return ? $this->language : true;
        }

        $langfile = str_replace('.php', '', $langfile);

        if ($add_suffix == true) {
            $langfile = str_replace('_lang.', '', $langfile).'_lang';
        }

        $langfile .= '.php';

        if (in_array($langfile, $this->is_loaded, true)) {
            return;
        }

        // Is there a possible module?
        $matches = explode('/', $langfile);
        $module = '';

        if (strpos($matches[0], '.php') === false) {
            $module = $matches[0];
            $langfile = str_replace($module . '/', '', $langfile);
        }

        unset($matches);

        $config =& get_config();

        if ($idiom == '') {
            $deft_lang = isset($config['language']) ? $config['language'] : $this->fallback;
            $idiom = ($deft_lang == '') ? $this->fallback : $deft_lang;
        }

        $lang = array();
        if ($idiom != $this->fallback) {
            $lang = $this->load($orig_langfile, $this->fallback, true, $add_suffix, $alt_path, $module);
        }

        // Determine where the language file is and load it
        $langfilePath = "language/{$idiom}/{$langfile}";
        if ($alt_path != '' && file_exists($alt_path . $langfilePath)) {
            include($alt_path . $langfilePath);
        } else {
            $found = false;
            $ci =& get_instance();

            if ($module != '') {
                $ci->load->add_module($module);
            }

            foreach ($ci->load->get_package_paths(true) as $package_path) {
                if (file_exists($package_path . $langfilePath)) {
                    include($package_path . $langfilePath);
                    $found = true;
                    break;
                }
            }

            if ($found !== true) {
                show_error("Unable to load the requested language file: {$langfilePath}");
            }
        }


        if (empty($lang)) {
            log_message('error', "Language file contains no data: {$langfilePath}");
            return;
        }

        if ($return == true) {
            return $lang;
        }

        $this->is_loaded[] = $langfile;
        $this->language = array_merge($this->language, $lang);
        unset($lang);

        log_message('debug', "Language file loaded: {$langfilePath}");
        return true;
    }

    // --------------------------------------------------------------------
}