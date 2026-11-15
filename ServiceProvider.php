<?php
namespace ElegenceIO\Bootstrap;

use ElegenceIO\Containers\Container;
use ElegenceIO\Contracts\Containers\ServiceProviders;
use Exception;

class ServiceProvider
{

    /**
     * Service Provider 
     * version 1.0.
     * namespace ElgenceIO\Core\Providers;
     * Modified : 19/09/2026
     */

    
    private ?array $providers = null;

    public function __construct(private array &$data, private ?Container $container)
    {
        $this->data = $data;
        $this->providers = $data["providers"];
        $this->container = $container;
    }

    /**
     * Gatheres array of Providers and Builds them
     * @return void
     * 
     */

    
    
    public function build():void
    {
        $providers = [];
        $defaults = config("app.defaults");
        // $defaults=[];

        foreach($defaults as $default)
        {
            if(!\class_exists($default))
            {
                throw new Exception("Cannot find called Class $default");
            }
        }

        $this->providers = !empty($defaults) ? array_merge($defaults,$this->providers) : $this->providers;

         foreach($this->providers as $providerClass)
        {
            // Check if the class Exists or doesnt 
            if(!class_exists($providerClass))
            {
                throw new Exception("Class {$providerClass} Cannot be loaded");
            }

            $provider = new $providerClass();

            $this->isInstance($provider,$providerClass);
            $this->hasDependencies($provider);
            $providers[] = $provider;
            $provider->register($this->container);
        }

            foreach($providers as $provider)
            {
                $provider->boot($this->container);
            }
            
    }
    /**
     * Valiadate if ServiceProvider implements Service Provider
     * namespace /ElegenceIO/Contacts/Containers/ServiceProviders
     * @param object $provider
     * @param string $providerClass
     * @return void
     */
    private function isInstance(object $provider,string $providerClass):void
    {
        if(!$provider instanceof ServiceProviders)
        {
            throw new Exception("{$providerClass} is not an instance of Service Provider interface");
        }
    }


    /**
     * validates is External ServiceProviders Require Dependencies
     * @param object $provider
     * @return void
     */
    private function hasDependencies(object $provider):void
    {
        if(!empty($provider->dependsOn()))
            {
                foreach($provider->dependsOn() as $dependency)
                {
                    if(!\class_exists($dependency))
                    {
                        throw new Exception("Provide Dependency class does not exist");
                    }
                }
            }
    }


}