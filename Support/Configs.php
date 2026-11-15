<?php
namespace ElegenceIO\Bootstrap\Support;

use DirectoryIterator;
use Exception;

final class Configs
{
    private ?string $basepath = null;
    private ?array $parts = [];
    public function __construct(?string $basepath =null)
    {
        if(!is_null($basepath))
        {
            $this->basepath = $basepath;
        }

        $this->register();
    }

    public function fromBase(string $basepath):void
    {
        $this->basepath = $basepath;
    }

    public function register()
    {
        $directories = $this->basepath;
        $parts = [];
        foreach(new DirectoryIterator($directories) as  $path)
        {
            // if(!\is_file($path)) continue;
            if($path->isDot()) continue;
            $filepath = $path->getFilename();
            [$name,$ext] = explode(".",$filepath);
            $name = \ucfirst($name);
            $this->parts[$name] = $this->registerFilePath($filepath);
        }
    }

    private function registerFilePath(string $filepath):string
    {
        return $this->basepath.\DIRECTORY_SEPARATOR.$filepath;
    }

    private function nestData($values)
    {

    }

    public function load(string $name):array|string
    {
        $keys = explode(".",$name,2);
        $filename = ucfirst(\array_shift($keys));
        $values = $keys[1] ?? null;
        if(array_key_exists($filename,$this->parts))
        {
            // throw error;
        }

        $data = include($this->parts[$filename]);

        foreach($keys as $key)
        {
            if(!is_array($data) || !\array_key_exists($key,$data))
            {
                // throw an exception
                throw new Exception("Key $key not registered");
            }

            $data = $data[$key];
        }
        
        return $data;


    }
}