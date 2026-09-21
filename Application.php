<?php
namespace ElegenceIO\Core\Bootstrap;

use ElegenceIO\Containers\Container;
use ElegenceIO\Core\Bootstrap\Kernel;
use ElegenceIO\Support\Structure\Files;
use Exception;
use ElegenceIO\Support\Types\Arrays;
use ElegenceIO\Support\Types\Strings;


class Application
{

    private array $data = [];
    private array $flags = [];
    
    // Set Predefinded Paths
    private string $configs = "Configs";

    private function setBase(string $basepath):void
    {
        $this->data["basepath"] =  rtrim($basepath,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    public function withConfigs(?string $configs=null)
    {
        return $this->data["configs"] = $configs;
    }

    public static function configure(string $basepath):self
    {
        // The Providers must be held in the Directory Of the Specified Basepath

        $instance = new self(); 
        $instance->setBase($basepath);
        $instance->flags["configure"] = true;
        return $instance;
    }
    
    public function providers(string|array $data) : self 
    {
        if(Arrays::exists("providers",$this->data))
        {
            throw new Exception("Mapping Failed Providers has already Been Instantiated");
        }

        $this->data["providers"] = $this->getType($data);
        return $this;   
    }    


    public function router(string|array $routes):self
    {
        if(Strings::is($routes))
        {
            $this->data["router"][] = $routes;
        }

        if(Arrays::is($routes))
        {
            $this->data["router"] = $routes;
        }
        return $this;
    }

    public function create():Dispatcher
    {
        if(!isset($this->data["configs"])) $this->data["configs"] = $this->configs;
        return new Dispatcher($this->data);
    } 

    
    private function getType(array|string $paths):string|array
    {
        if(Strings::is($paths))
        {
            if(!Files::has($paths)){
                throw new Exception("File $paths cannot be found");
            }
            
            $data = include $paths;
        }

        if(Arrays::is($paths))
        {   foreach($paths as $path)
            {
                if(!Files::has($path))
                {
                     throw new Exception("File $paths cannot be found");
                }
            }
            $data = $paths;
        }

        return $data;
    }

}


?>