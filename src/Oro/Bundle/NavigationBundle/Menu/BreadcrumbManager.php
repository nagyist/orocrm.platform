<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\Util\MenuManipulator;

/**
 * Provides breadcrumbs by the menu helps to find menu items
 */
class BreadcrumbManager implements BreadcrumbManagerInterface
{
    /**
     * @var MatcherInterface
     */
    protected $matcher;

    /**
     * @var MenuProviderInterface
     */
    protected $provider;

    public function __construct(MenuProviderInterface $provider, MatcherInterface $matcher)
    {
        $this->matcher = $matcher;
        $this->provider = $provider;
    }

    #[\Override]
    public function getBreadcrumbs($menuName, $isInverse = true, $route = null)
    {
        $menu = $this->getMenu($menuName);
        if ($route === null) {
            $currentItem = $this->getCurrentMenuItem($menu);
        } else {
            $currentItem = $this->getMenuItemByRoute($menu, $route);
        }

        if ($currentItem) {
            return $this->getBreadcrumbArray($menuName, $currentItem, $isInverse);
        }

        return null;
    }

    #[\Override]
    public function getMenu($menu, array $pathName = [], array $options = [])
    {
        if (!$menu instanceof ItemInterface) {
            $menu = $this->provider->get((string) $menu, array_merge($options, ['check_access_not_logged_in' => true]));
        }
        foreach ($pathName as $child) {
            $menu = $menu->getChild($child);
            if ($menu === null) {
                throw new \InvalidArgumentException(sprintf('The menu has no child named "%s"', $child));
            }
        }

        return $menu;
    }

    #[\Override]
    public function getCurrentMenuItem($menu)
    {
        foreach ($menu as $item) {
            if ($this->matcher->isCurrent($item)) {
                return $item;
            }

            if ($item->getChildren() && $currentChild = $this->getCurrentMenuItem($item)) {
                return $currentChild;
            }
        }

        return null;
    }

    /**
     * Find menu item by route
     *
     * @param ItemInterface $menu
     * @param string $route
     * @return ItemInterface|null
     */
    public function getMenuItemByRoute($menu, $route)
    {
        foreach ($menu as $item) {
            /** @var $item ItemInterface */

            $routes = (array)$item->getExtra('routes', []);
            if ($this->match($routes, $route)) {
                return $item;
            }

            if ($item->getChildren() && $currentChild = $this->getMenuItemByRoute($item, $route)) {
                if ($currentChild->getExtra('skip_breadcrumbs', false)) {
                    return $item;
                }

                return $currentChild;
            }
        }

        return null;
    }

    #[\Override]
    public function getBreadcrumbArray($menuName, $item, $isInverse = true)
    {
        $manipulator = new MenuManipulator();
        $breadcrumbs = $manipulator->getBreadcrumbsArray($item);
        if ($breadcrumbs[0]['label'] == $menuName) {
            unset($breadcrumbs[0]);
        }

        if (!$isInverse) {
            $breadcrumbs = array_reverse($breadcrumbs);
        }

        return $breadcrumbs;
    }

    #[\Override]
    public function getBreadcrumbLabels($menu, $route)
    {
        $labels = [];
        $menuItem = $this->getMenuItemByRoute($this->getMenu($menu), $route);
        if ($menuItem) {
            $breadcrumb = $this->getBreadcrumbArray($menu, $menuItem, false);
            foreach ($breadcrumb as $breadcrumbItem) {
                $labels[] = $breadcrumbItem['label'];
            }
        }

        return $labels;
    }

    /**
     * Match routes
     *
     * @param array $routes
     * @param $route
     * @return bool
     */
    protected function match(array $routes, $route)
    {
        foreach ($routes as $testedRoute) {
            if (!$this->routeMatch($testedRoute, $route)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Match routes
     *
     * @param string $pattern
     * @param string $route
     * @return boolean
     */
    protected function routeMatch($pattern, $route)
    {
        if ($pattern == $route) {
            return true;
        } elseif (str_starts_with($pattern, '/') && strlen($pattern) - 1 === strrpos($pattern, '/')) {
            return preg_match($pattern, $route);
        } elseif (str_contains($pattern, '*')) {
            $pattern = sprintf('/^%s$/', str_replace('*', '\w+', $pattern));
            return preg_match($pattern, $route);
        } else {
            return false;
        }
    }

    #[\Override]
    public function supports($route = null)
    {
        return true;
    }
}
