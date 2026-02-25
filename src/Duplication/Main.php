<?php

declare(strict_types=1);

namespace Duplication;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\world\Position;
use pocketmine\math\Vector3;

class Main extends PluginBase {

    protected function onEnable(): void {
        $this->saveDefaultConfig();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        if(!$sender instanceof Player){
            return true;
        }

        if($command->getName() === "dupe"){

            if(!$sender->hasPermission("dupe.allowed")){
                $sender->sendMessage($this->getConfig()->get("messages")["no-permission"]);
                return true;
            }

            $world = $sender->getWorld();
            $direction = $sender->getDirectionVector()->normalize();
            $pos = $sender->getPosition()->add(
                (int) round($direction->getX()),
                0,
                (int) round($direction->getZ())
            );

            $pos = $pos->floor();

            // Place first chest
            $world->setBlock($pos, VanillaBlocks::CHEST());

            // Place second chest next to it (east side)
            $secondPos = $pos->add(1, 0, 0);
            $world->setBlock($secondPos, VanillaBlocks::CHEST());

            // Get chest tiles
            $tile1 = $world->getTile($pos);
            $tile2 = $world->getTile($secondPos);

            if($tile1 instanceof TileChest && $tile2 instanceof TileChest){

                // Pair them into double chest
                $tile1->pairWith($tile2);
                $inventory = $tile1->getInventory();

                // Copy player inventory into chest
                foreach($sender->getInventory()->getContents() as $item){
                    $inventory->addItem(clone $item);
                }

                $sender->sendMessage($this->getConfig()->get("messages")["success"]);
            }

            return true;
        }

        return false;
    }
}
