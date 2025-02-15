<?php
/**
 *  _____    ____    ____   __  __  __  ______
 * |  __ \  / __ \  / __ \ |  \/  |/_ ||____  |
 * | |__) || |  | || |  | || \  / | | |    / /
 * |  _  / | |  | || |  | || |\/| | | |   / /
 * | | \ \ | |__| || |__| || |  | | | |  / /
 * |_|  \_\ \____/  \____/ |_|  |_| |_| /_/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace room17\SkyBlock\island;


use pocketmine\level\Level;
use room17\SkyBlock\event\island\IslandOpenEvent;
use room17\SkyBlock\event\island\IslandCloseEvent;
use room17\SkyBlock\SkyBlock;

class IslandManager {

    /** @var SkyBlock */
    private $plugin;

    /** @var Island[] */
    private $islands = [];

    /**
     * IslandManager constructor.
     * @param SkyBlock $plugin
     */
    public function __construct(SkyBlock $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents(new IslandListener($this), $plugin);
    }

    /**
     * @return SkyBlock
     */
    public function getPlugin(): SkyBlock {
        return $this->plugin;
    }

    /**
     * @return Island[]
     */
    public function getIslands(): array {
        return $this->islands;
    }

    /**
     * @param string $identifier
     * @return null|Island
     */
    public function getIsland(string $identifier): ?Island {
        return $this->islands[$identifier] ?? null;
    }

    /**
     * @param Level $level
     * @return Island|null
     */
    public function getIslandByLevel(Level $level): ?Island {
        return $this->getIsland($level->getName());
    }

    /**
     * @param string $identifier
     * @param array $members
     * @param bool $locked
     * @param string $type
     * @param Level $level
     * @param int $blocksBuilt
     * @throws \ReflectionException
     */
    public function openIsland(string $identifier, array $members, bool $locked, string $type, Level $level, int $blocksBuilt): void {
        $this->islands[$identifier] = new Island($this, $identifier, $members, $locked, $type, $level, $blocksBuilt);
        (new IslandOpenEvent($this->islands[$identifier]))->call();
    }

    /**
     * @param Island $island
     * @throws \ReflectionException
     */
    public function closeIsland(Island $island): void {
        $island->save();
        $server = $this->plugin->getServer();
        (new IslandCloseEvent($island))->call();
        $server->unloadLevel($island->getLevel());
        unset($this->islands[$island->getIdentifier()]);
    }

}