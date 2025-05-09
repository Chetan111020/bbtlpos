<?php

namespace App\Http;

use Nwidart\Menus\Presenters\Presenter;
use App\Transaction;

class UiCustomPresenter extends Presenter
{
    /**
     * {@inheritdoc }.
     */
    public function getOpenTagWrapper()
    {
        return PHP_EOL . '<ul class="sidebar-menu tree" data-widget="tree">' . PHP_EOL;
    }

    /**
     * {@inheritdoc }.
     */
    public function getCloseTagWrapper()
    {
        return PHP_EOL . '</ul>' . PHP_EOL;
    }

    /**
     * {@inheritdoc }.
     */
    public function getMenuWithoutDropdownWrapper($item)
    {
         $business_id = request()->session()->get('user.business_id');
        $transaction = Transaction:: where('transactions.business_id', $business_id)
        ->where('transactions.type', 'sell')
        ->where('transactions.status', 'draft')
        ->where('transactions.woocommerce_order_id','!=','null')
        ->get();

        $i = $transaction->count();

        $html = '';
        if($item->title == 'List Drafts')
        {
            $html = '<span style="margin: 0 0 0 5px;" class="badge bg-green">'.$i.'</span>';
        }
        $svg_icon = $this->getSVG($item->title);
        $target_class = $svg_icon != "" ? 'sidebar_hover_target' : '';
        return '<li' . $this->getActiveState($item) . '><a href="' . $item->getUrl() . '" ' . $item->getAttributes() . '>' . $svg_icon . ' <span class="' . $target_class . '">' . $item->title . '</span>'.$html.'</a></li>' . PHP_EOL;
        // return '<li' . $this->getActiveState($item) . '><a href="' . $item->getUrl() . '" ' . $item->getAttributes() . '>' . $item->getIcon() . ' <span>' . $item->title . '</span></a></li>' . PHP_EOL;
    }

    /**
     * {@inheritdoc }.
     */
    public function getActiveState($item, $state = ' class="active"')
    {
        return $item->isActive() ? $state : null;
    }

    /**
     * Get active state on child items.
     *
     * @param $item
     * @param string $state
     *
     * @return null|string
     */
    public function getActiveStateOnChild($item, $state = 'active')
    {
        return $item->hasActiveOnChild() ? $state : null;
    }

    /**
     * {@inheritdoc }.
     */
    public function getDividerWrapper()
    {
        return '<li class="divider"></li>';
    }

    /**
     * {@inheritdoc }.
     */
    public function getHeaderWrapper($item)
    {
        return '<li class="header">' . $item->title . '</li>';
    }

    /**
     * {@inheritdoc }.
     */
    public function getMenuWithDropDownWrapper($item)
    {
        $svg_icon = $this->getSVG($item->title);
        return '<li class="treeview' . $this->getActiveStateOnChild($item, ' active') . '" ' . $item->getAttributes() . '>
		          <a href="#">
					' . $svg_icon . ' <span class="sidebar_hover_target">' . $item->title . '</span>
                    <span class="sidebar_hover_target pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
			      </a>
			      <ul class="treeview-menu">
			      	' . $this->getChildMenuItems($item) . '
			      </ul>
		      	</li>'
        . PHP_EOL;
    }

    /**
     * Get multilevel menu wrapper.
     *
     * @param \Nwidart\Menus\MenuItem $item
     *
     * @return string`
     */
    public function getMultiLevelDropdownWrapper($item)
    {
        return '<li class="treeview' . $this->getActiveStateOnChild($item, ' active') . '">
		          <a href="#">
					' . $item->getIcon() . ' <span>' . $item->title . '</span>
			      	<span class="pull-right-container">
                      <i class="fa fa-angle-left pull-right"></i>
                    </span>
			      </a>
			      <ul class="treeview-menu">
			      	' . $this->getChildMenuItems($item) . '
			      </ul>
		      	</li>'
        . PHP_EOL;
    }

    public function getSVG($name){
        $svg_icon = '';
        if($name == 'Home'){
            $svg_icon = 'home.svg';
        }
        else if($name == 'User Management'){
            $svg_icon = 'user.svg';
        }
        else if($name == 'Profiles'){
            $svg_icon = 'profile.svg';
        }
        else if($name == 'Items'){
            $svg_icon = 'product.svg';
        }
        else if($name == 'Purchases'){
            $svg_icon = 'purchase.svg';
        }
        else if($name == 'All Orders'){
            $svg_icon = 'order.svg';
        }
        else if($name == 'Inventory Adjustment'){
            $svg_icon = 'tasks.svg';
        }
        else if($name == 'Expenses'){
            $svg_icon = 'cost.svg';
        }
        else if($name == 'Payment Accounts'){
            $svg_icon = 'payment.svg';
        }
        else if($name == 'Reports'){
            $svg_icon = 'report.svg';
        }
        else if($name == 'CRM'){
            $svg_icon = 'crm.svg';
        }
        else if($name == 'Order Queue'){
            $svg_icon = 'queue.svg';
        }
        else if($name == 'Order Delivery'){
            $svg_icon = 'adjustment.svg';
        }
        else if($name == 'Notification Templates'){
            $svg_icon = 'notification.svg';
        }
        else if($name == 'Settings'){
            $svg_icon = 'settings.svg';
        }
        else if($name == 'Woocommerce'){
            $svg_icon = 'web.svg';
        }
        else{
            return '';
        }
        return "<img src='/v2-assets/sidebar-svg/" . $svg_icon . "' />";
    }
}
