<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\Apps\MenuModel;
use App\Models\Apps\AppsModel;

abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'form'];

    /**
     * Instance of the MenuModel.
     *
     * @var \App\Models\Apps\MenuModel
     */
    protected $MenuModel;
    protected $apps;
    protected $AppsModel;

    /**
     * Menu data to be passed to views.
     *
     * @var array
     */ 
    protected $menus = [];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->MenuModel = new MenuModel();
        $this->apps = new AppsModel();
        $userId = session()->get('userid');
        if ($userId) { 
            $menus = $this->MenuModel->getMenusPermissions($userId);
            
            // Fix N+1 query: fetch all submenus once
            $allSubmenus = $this->MenuModel->getAllSubMenus((int) $userId);
            $submenusByParent = [];
            foreach ($allSubmenus as $sub) {
                $submenusByParent[(int) $sub['parent_id']][] = $sub;
            }

            foreach ($menus as &$menu) {
                $menu['submenus'] = $submenusByParent[(int) $menu['id']] ?? [];
            }
            $this->menus = $menus;
        }
    }

    public function renderView($view, $data = [])
    {
        $userId = session()->get('userid');
        $menuId = session()->get('active_menus');
        if (empty($menuId)) {
            $menuId = $this->MenuModel->findPermissionIdByUrl(trim($this->request->getUri()->getPath(), '/'));
        }
        $permit = $this->MenuModel->getPermissions($userId, $menuId);
        $avatar = $this->apps->getAvatar($userId);
        $data = array_merge(
            [
                'menus' => $this->menus,
                'permit' => $permit,
                'avatar' => $avatar,
                'seslog' => session()->get(),
            ],
            $data
        );
        return view($view, $data);
    }
}
