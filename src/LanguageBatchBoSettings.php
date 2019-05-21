<?php
namespace Language;


final class LanguageBatchBoSettings
{
    protected $settings = [];

    public function __construct()
    {
        $this->settings['CACHE_PATH'] = '/cache';
        $this->settings['APPLET_FILE_PREFIX'] = 'lang_';
        $this->settings['XML_FILES_PATH'] = $this->settings['CACHE_PATH'] . '/flash';

        $this->settings['APPLICATIONS'] = Config::get('system.translated_applications');
        $this->settings['ROOT_PATH'] = Config::get('system.paths.root');

        $this->settings['APPLETS'] = [
            'memberapplet' => 'JSM2_MemberApplet'
        ];
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function get($key)
    {
        if (!isset($this->settings[$key])) {
            throw new \Exception("Undefined index $key");
        }

        return $this->settings[$key];
    }

    public function set($key, $value)
    {
        $this->settings[$key] = $value;
        return $this->settings[$key];
    }
}