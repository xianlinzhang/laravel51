<?php
namespace King\ExtensionForLaravel;
use Illuminate\Session\SessionManager;
use Illuminate\Config\Repository;
class Extension
{
    /**
     * @var SessionManager
     */
    protected $session;
    /**
     * @var Repository
     */
    protected $config;
    /**
     * Packagetest constructor.
     * @param SessionManager $session
     * @param Repository $config
     */
    public function __construct(SessionManager $session, Repository $config)
    {
        $this->session = $session;
        $this->config = $config;
    }
    /**
     * @param string $msg
     * @return string
     */
    public function test_rtn($msg = ''){
        $config_arr = $this->config->get('extension.options');
        return $msg.' <strong>from your custom develop package!</strong>>';
    }
}