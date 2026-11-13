<?php
namespace ElegenceIO\Bootstrap;

use DirectoryIterator;
use ElegenceIO\Containers\ContainerRegistry;
use ElegenceIO\Containers\Container;
use ElegenceIO\Contracts\Bootstrap\BootstrapKernel;
use ElegenceIO\Core\Filesystem\Configs;
use ElegenceIO\Core\Requests\Console\Kernel;
use ElegenceIO\Helpers\Helpers;
use ElegenceIO\Core\Requests\Http\Kernel as HttpKernel;
use ElegenceIO\Bootstrap\ServiceProvider;
use ElegenceIO\Foundation\Parsers\Env;
use ElegenceIO\Logger\Log;
use Exception;

final class Dispatcher
{

    private ?Container $container = null;

    /**
     * Bootstrap Configuator
     * version 1.0
     * 
     */
    public function __construct(private array $data)
    {
        $this->data = $data; 
        $this->StartContainer();
    }

    private function setConfigs()
    {   
        $this->container->bind("config",new Configs(basepath($this->data["configs"])));
    }
    private function StartContainer()
    {
        $this->container = new Container();
        $this->container->bind("app",$this);
        $this->container->bind("container",$this->container);
        $this->container->make(Helpers::class);
        ContainerRegistry::set($this->container);

     }



    private function setbase()
    {
        app()->container->bind("basepath",$this->data["basepath"]);
    }

    private function router()
    {
        if(!empty($this->data["router"]))
        {
            foreach($this->data["router"] as $router)
            {
                require_once $router;
            }
        }
    }


    protected function preBootstrap()
    {  
        $this->setbase();
        $this->container->bind("env",new Env($this->data["basepath"]));
        $this->container->bind("logger",new Log(basepath("/Storage/Logs.php")));
        $this->setConfigs();
    }

    public function postBootstrap()
    {
          // Register ServiceProvider to Allow Externals
          if(isset($this->data["providers"]));
            {       
            app("container")->bind("providers",new ServiceProvider($this->data,$this->container));
            }
        app("providers")->build($this->container);
        $this->router();
        app("container")->remove("providers");
    }

    public function handle(mixed $requests):object
    {
        $this->preBootstrap();
        $handle = (\php_sapi_name() !== "cli") ? new HttpKernel($this->container,$requests) : new Kernel($this->container,$requests);
        $this->postBootstrap();
        // $this->container->bind("view",new View(basepath()));
        // echo view("Home/Index.php")->with("username","martin")->render();
        if(!$handle instanceof BootstrapKernel)
        {
            throw new Exception("Failed to load Boostrapper");
        }
        
        return $handle->boot();

    }
}
