<?php

namespace ZfcUserAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator;
use Zend\Stdlib\Hydrator\ClassMethods;
use ZfcUser\Mapper\UserInterface;
use ZfcUser\Options\ModuleOptions as ZfcUserModuleOptions;
use ZfcUserAdmin\Options\ModuleOptions;
use Zend\EventManager\EventManager;
use ZfcUserAdmin\Event\ZfcUserAdminEvent;
use Doctrine\ORM\EntityManager;
use ZfcDatagrid\Column;
use ZfcUserAdmin\Collection\ButtonCollection;
use ZfcUserAdmin\Collection\ColumnCollection;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerInterface;
use ZfcUserAdmin\Event\ListEvent;

class UserAdminController extends AbstractActionController implements EventManagerAwareInterface
{
    use  EventManagerAwareTrait;
    
    private $eventIdentifier = array('ZfcUserAdmin', ZfcUserAdminEvent::class);
    
    protected $options, $userMapper;
    protected $zfcUserOptions;
    /**
     * @var \ZfcUserAdmin\Service\User
     */
    protected $adminUserService;
    
    /**
     * @return EventManagerInterface
     */
    public function createPrivateEventManager($eventClazz)
    {
        $events = new EventManager();
        $events->setIdentifiers(array('ZfcUserAdmin',$eventClazz));
        $events->setEventClass($eventClazz);
        return $events;
    }

    
    public function listAction()
    {
        /* @var $grid \ZfcDatagrid\Datagrid */
        /* @var $em EntityManager */
        
        $serviceLocator = $this->getServiceLocator();
        $config = $serviceLocator->get('config');
        $entityClass = $config['zfcuser']['userEntityClass'];
        $grid = $serviceLocator->get('ZfcDatagrid\Datagrid');
        $em = $serviceLocator->get(EntityManager::class);
        
        $dqlUserAlias = 'e';
        $qb = $em->getRepository($entityClass)->createQueryBuilder($dqlUserAlias);
        
        $grid->setTitle('Users');
        $columnCollection = new ColumnCollection();
        $buttonCollection = new ButtonCollection();
        
        $colId = new Column\Select('id', 'e');
        $colId->setLabel('User ID');
        $columnCollection->put(ColumnCollection::$ID_COLUMN_ID, $colId);
         
        $colUsername = new Column\Select('username', 'e');
        $colUsername->setLabel('Username');
        $columnCollection->put(ColumnCollection::$ID_COLUMN_USERNAME, $colUsername);
        
        $colEmail = new Column\Select('email', 'e');
        $colEmail->setLabel('Email');
        $columnCollection->put(ColumnCollection::$ID_COLUMN_EMAIL, $colEmail);
        
        $actions = new Column\Action();
        $actions->setLabel('#Action');
         
        $editBtn = new Column\Action\Button();
        $editBtn->setLabel('Edit');
        $editBtn->setAttribute('class', 'btn btn-primary btn-sm');
        $editBtn->setLink($this->url()->fromRoute('zfcadmin/zfcuseradmin/create', array( 'userId' => $colId->getColumnValuePlaceholder($colId))));
        $buttonCollection->put(ButtonCollection::$ID_EDIT_BTN, $editBtn);
         
        $deleteBtn = new Column\Action\Button();
        $deleteBtn->setLabel('Delete');
        $deleteBtn->setAttribute('class', 'btn btn-danger btn-sm delete-btn');
        $deleteBtn->setLink($this->url()->fromRoute('zfcadmin/zfcuseradmin/remove', array( 'userId' => $colId->getColumnValuePlaceholder($colId))));
        $buttonCollection->put(ButtonCollection::$ID_DELETE_BTN, $deleteBtn);
        
        $events = $this->createPrivateEventManager(ListEvent::class);
        $events->trigger(ListEvent::$EVENT_NAME, $this, array('queryBuilder' => $qb, 'buttonCollection'=>$buttonCollection, 'columnCollection'=>$columnCollection ));
        
        foreach ($buttonCollection as $btn){
            $actions->addAction($btn);
        }
        foreach ($columnCollection as $column){
            $grid->addColumn($column);
        }
        $grid->addColumn($actions);
        $grid->setDataSource($qb);
         
        // Finalizing
        $grid->setToolbarTemplateVariables(array(
            'addUrl' => $this->url()->fromRoute('zfcadmin/zfcuseradmin/create')
        ));
        
        
    }
    

    public function createAction()
    {
        /** @var $form \ZfcUserAdmin\Form\CreateUser */
        $form = $this->getServiceLocator()->get('zfcuseradmin_createuser_form');
        $request = $this->getRequest();

        /** @var $request \Zend\Http\Request */
        if ($request->isPost()) {
            $zfcUserOptions = $this->getZfcUserOptions();
            $class = $zfcUserOptions->getUserEntityClass();
            $user = new $class();
            $form->setHydrator(new ClassMethods());
            $form->bind($user);
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user = $this->getAdminUserService()->create($form, (array)$request->getPost());
                if ($user) {
                    $this->flashMessenger()->addSuccessMessage('The user was created');
                    return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/list');
                }
            }
        }

        return array(
            'createUserForm' => $form
        );
    }

    public function editAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');
        $user = $this->getUserMapper()->findById($userId);

        /** @var $form \ZfcUserAdmin\Form\EditUser */
        $form = $this->getServiceLocator()->get('zfcuseradmin_edituser_form');
        $form->setUser($user);

        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $user = $this->getAdminUserService()->edit($form, (array)$request->getPost(), $user);
                if ($user) {
                    $this->flashMessenger()->addSuccessMessage('The user was edited');
                    return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/list');
                }
            }
        } else {
            $form->populateFromUser($user);
        }

        return array(
            'editUserForm' => $form,
            'userId' => $userId
        );
    }

    public function removeAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');

        /** @var $identity \ZfcUser\Entity\UserInterface */
        $identity = $this->zfcUserAuthentication()->getIdentity();
        if ($identity && $identity->getId() == $userId) {
            $this->flashMessenger()->addErrorMessage('You can not delete yourself');
        } else {
            $user = $this->getUserMapper()->findById($userId);
            if ($user) {
                $this->getUserMapper()->remove($user);
                $this->flashMessenger()->addSuccessMessage('The user was deleted');
            }
        }

        return $this->redirect()->toRoute('zfcadmin/zfcuseradmin/list');
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceLocator()->get('zfcuseradmin_module_options'));
        }
        return $this->options;
    }

    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceLocator()->get('zfcuser_user_mapper');
        }
        return $this->userMapper;
    }

    public function setUserMapper(UserInterface $userMapper)
    {
        $this->userMapper = $userMapper;
        return $this;
    }

    public function getAdminUserService()
    {
        if (null === $this->adminUserService) {
            $this->adminUserService = $this->getServiceLocator()->get('zfcuseradmin_user_service');
        }
        return $this->adminUserService;
    }

    public function setAdminUserService($service)
    {
        $this->adminUserService = $service;
        return $this;
    }

    public function setZfcUserOptions(ZfcUserModuleOptions $options)
    {
        $this->zfcUserOptions = $options;
        return $this;
    }

    /**
     * @return \ZfcUser\Options\ModuleOptions
     */
    public function getZfcUserOptions()
    {
        if (!$this->zfcUserOptions instanceof ZfcUserModuleOptions) {
            $this->setZfcUserOptions($this->getServiceLocator()->get('zfcuser_module_options'));
        }
        return $this->zfcUserOptions;
    }
}
