<?php
namespace ElegenceIO\Core\Bootstrap;

use ElegenceIO\Core\Bootstrap\Processing\Binding;
use ElegenceIO\Core\Bootstrap\Processing\Configurator;
use ElegenceIO\Core\Bootstrap\Processing\Registration;
use ElegenceIO\Containers\ContainerRegistry;
use ElegenceIO\Containers\Container;
use ElegenceIO\Core\Http\Kernel as Http;
use ElegenceIO\Core\Console\Kernel as Console;
use ElegenceIO\Contracts\Bootstrap\BootstrapKernel;
use ElegenceIO\Support\Structure\Files;
use ElegenceIO\Support\Types\Arrays;
use Exception;

class Kernel
{
    private ?Binding $binding = null;
    private ?Registration $register = null;

    private ?Container $container = null;
    public function __construct(private array $data)
    {
        $this->data = $data;
        $this->startContainer();
        $this->binding = new Binding($this->container,$this->data);
        $this->register = new Registration($this->container,$this->data);
    }

    /**
     * private @method startContainer
     * @return void
     *
     */

    private function startContainer():void
    {
        $this->container = new Container();
        ContainerRegistry::set($this->container);
    }

    /**
     * private @method RegisterHelpers()
     * @return void
     */

    /**
     * private @method loadRouters
     */
    private function loadRouters():void
    {
        $router = $this->data["router"];
        // Valiate Routing and load
        if(!empty($router))
        {
            foreach($router as $route)
            {
                if(Files::has($route))
                {
                    require_once $route;
                }
            }
        }

        unset($router);
    }
    
    /**
     * Prebootstrap Setup
     * @method preBootstrap()
     *  @includes start containers basepath
     * 
     */

    private function preBootstrap()
    {
        $this->register->helpers();
        $this->container->bind("basepath",$this->data["basepath"]);
        // $this->register->paths();
        // $this->register->configurator();
    }

    


    private function postBootstrap()
    {
        // $this->container->make("configurator")->create();
        $this->register->providers();

        if($this->container->has("basepath"))
        {
            // echo "Basepath Found";
            echo $this->container->make("basepath");
        }
        else
        {
            // echo "Not Bound";
        }
        $this->loadRouters();
    }

    public function handle(mixed $requests):object
    {
        // this strtd the containeers and sets the baspath
        $this->preBootstrap();
        $this->postBootstrap();

        $handle = (\php_sapi_name() !== "cli") ? new Http($this->container,$requests) : new Console($this->container,$requests);
        
        if(!$handle instanceof BootstrapKernel)
        {
            throw new Exception("Failed to load Boostrapper");
        }
        return $handle->boot();
    }
}