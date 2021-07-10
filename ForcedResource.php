<?php

/**
 * @name ForcedResource
 * @author Security-Development
 * @main ForcedResource\ForcedResource
 * @version 0.1.0
 * @api 3.10.0
 */

namespace ForcedResource;

use pocketmine\{
  plugin\PluginBase,
  resourcepacks\ZippedResourcePack,
  Server
};


class ForcedResource extends PluginBase
{

  protected $resourcePack = [];

  function onEnable(): void
  {
    $this->saveResource("manual.txt", true);

    $resource = Server::getInstance()->getResourcePackManager();
    $class = new \ReflectionClass($resource);

    $dir = $this->getDataFolder();
    if(is_dir($dir))
    {
      if($open = opendir($dir)):
        while(($files = readdir($open)) !== false)
        {
          if($files == '.' || $files == '..') continue;
          if(is_file($dir. $files))
          {
            $file_name = substr(strrchr($files, '.'), 1);
            if($file_name == 'zip' or $file_name == 'mcpack')
            {
              echo "\n리소스팩 파일 : ". $files . " 활성화 성공!!\n";
              $this->resourcePack[] = $this->getDataFolder(). $files;
            }else{
              echo "\n". $files . "파일은 리소스팩이 아닙니다.\n";
            }
          }
        }
        closedir($open);
          echo "\n". count($this->resourcePack) ."개의 리소스팩을 활성화 시켰습니다.\n\n";
      endif;

      foreach($this->resourcePack as $key)
      {
        $pack = new ZippedResourcePack($key);

        $resource_Property = $class->getProperty("resourcePacks");
        $resource_Property->setAccessible(true);
        $currentResourcePacks = $resource_Property->getValue($resource);
        $currentResourcePacks[] = $pack;
        $resource_Property->setValue($resource, $currentResourcePacks);

        $uuid_Property = $class->getProperty("uuidList");
        $uuid_Property->setAccessible(true);
        $uuidPacks = $uuid_Property->getValue($resource);
        $uuidPacks[strtolower($pack->getPackId())] = $pack;
        $uuid_Property->setValue($resource, $uuidPacks);

        $server_Property = $class->getProperty("serverForceResources");
        $server_Property->setAccessible(true);
        $serverPacks = $server_Property->getValue($resource);
        $server_Property->setValue($resource, true);

      }
    }
  }
}
