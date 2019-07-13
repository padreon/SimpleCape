<?php
/*
 *                      _
 *  _ __     __ _    __| |  _ __    ___    ___    _ __
 * | '_ \   / _` |  / _` | | '__|  / _ \  / _ \  | '_ \
 * | |_) | | (_| | | (_| | | |    |  __/ | (_) | | | | |
 * | .__/   \__,_|  \__,_| |_|     \___|  \___/  |_| |_|
 * |_|
 *
 * Created by PhpStorm.
 * Date: 12/07/2019
 * Time: 19.36
 */

namespace padreon\SimpleCape;


use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{

    public function onEnable(){
        if (!extension_loaded("gd")){
            $this->getServer()->getLogger()->error("please enable gd!");
            $this->getServer()->disablePlugins();
            return true;
        }
        @mkdir($this->getDataFolder());
        $this->saveResource('cape.png');
        return true;
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if ($command->getName() == "cape"){
            if (!$sender->hasPermission("simple.cape")) {
                $sender->sendMessage(TextFormat::RED . "You don't have permission to use command");
                return true;
            }
            if ($sender->hasPermission("simple.cape")) {
                if (count($args) === 1) {
                    if ($sender instanceof Player) {
                        if ($args[0] == "remove") {
                            $this->setCape($sender, "");
                            return true;
                        }
                            $this->createCape($sender, $args[0]);
                        return true;
                    }
                }
                if (count($args) === 2) {
                    if (!$sender->hasPermission("simple.cape.other")) {
                        $sender->sendMessage(TextFormat::RED . "You don't have permission to use command");
                        return true;
                    }
                    $target = $this->getServer()->getPlayer($args[1]);
                    if ($target instanceof Player){
                        if ($target->isOnline()) {
                            if ($args[0] === "remove"){
                                $this->setCape($target, "", $sender);
                                return true;
                            }
                            $this->createCape($target, $args[0], $sender);
                            return true;
                        }
                        $sender->sendMessage(TextFormat::RED . "Player not online!");
                        return true;
                    }
                    $sender->sendMessage(TextFormat::RED . "Player not found");
                    return true;
                }
                $sender->sendMessage($command->getUsage());
                return true;
            }
        }
        return true;
    }
    /**
     * @param Player $player
     * @param string $file
     * @param CommandSender|null $sender
     * @return bool
     */
    public function createCape(Player $player, string $file, CommandSender $sender = null){
        $ex = '.png';
        $path = $this->getDataFolder() . $file . $ex;
        $img = imagecreatefrompng($path);
        $rgba = "";
        for($y = 0; $y < imagesy($img); $y++) {
            for($x = 0; $x < imagesx($img); $x++) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $rgba .= chr($r).chr($g).chr($b).chr(255);
            }
        }
        if (!strlen($rgba) == 8192){
            if (!$sender == null){
                $sender->sendMessage(TextFormat::RED . "Invalid cape");
                return true;
            }
            $player->sendMessage(TextFormat::RED . "Invalid cape");
            return true;
        }
        $this->setCape($player, $rgba, $sender);
        return true;
    }


    /**
     * @param Player $player
     * @param string $cape
     * @param CommandSender|null $sender
     * @return bool
     */
    public function setCape(Player $player, string $cape, CommandSender $sender = null){
        $oldSkin = $player->getSkin();
        $skin = [$oldSkin->getSkinId(), $oldSkin->getSkinData(), $cape, $oldSkin->getGeometryName(), $oldSkin->getGeometryData()];
        $newSkin = new Skin($skin[0], $skin[1], $skin[2], $skin[3], $skin[4]);
        $player->setSkin($newSkin);
        $player->sendSkin();
        if (!$cape == null) {
            if (!$sender == null) {
                $sender->sendMessage(TextFormat::GREEN . "Successfully add cape to " . $player->getName());
                return true;
            }
            $player->sendMessage(TextFormat::GREEN . "Successfully add cape");
            return true;
        }
        if (!$sender == null) {
            $sender->sendMessage(TextFormat::GREEN . "Successfully remove cape from"  . $player->getName());
            return true;
        }
        $player->sendMessage(TextFormat::GREEN . "Successfully remove cape");
        return true;

    }
}
