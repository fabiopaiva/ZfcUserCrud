<?php

namespace ZfcUserCrud\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\InputFilter\InputFilter;
use Zend\Crypt\Password\Bcrypt;
use ZfcUser\Options\UserServiceOptionsInterface;

class RoleController extends AbstractActionController {

    /**
     * ORM object manager
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getOM() {
        return $this
                        ->getServiceLocator()
                        ->get('Doctrine\ORM\EntityManager');
    }

    public function indexAction() {
        $config = $this->getServiceLocator()->get('zfcusercrud_options');
        $query = $this
                ->getOM()
                ->getRepository($config['roleEntity'])
                ->createQueryBuilder('q');
        $searchTerm = '';
        if ($this->getRequest()->isPost()) {
            $searchTerm = $this->params()->fromPost('searchTerm');
            $query
                    ->where('q.roleId LIKE :search1')
                    ->setParameter('search1', "%{$searchTerm}%")
            ;
        }
        $paginator = new Paginator(
                new DoctrinePaginator(new ORMPaginator($query))
        );
        $paginator
                ->setCurrentPageNumber($this->params()->fromQuery('page', 1))
                ->setItemCountPerPage(20);
        return array(
            'roles' => $paginator,
            'searchTerm' => $searchTerm
        );
    }

    public function newAction() {
        $translator = $this->getServiceLocator()->get('translator');
        $form = $this->getForm();
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $role = $form->getData();
                $this->getOM()->persist($role);
                $this->getOM()->flush();
                $this->flashMessenger()->addSuccessMessage($translator->translate('Role saved'));
                $this->redirect()->toRoute('zfc-user-crud-role');
            }
        }
        $form->prepare();
        return array(
            'form' => $form
        );
    }

    public function editAction() {
        $translator = $this->getServiceLocator()->get('translator');
        $config = $this->getServiceLocator()->get('zfcusercrud_options');
        $form = $this->getForm();
        $role = $this
                ->getOM()
                ->getRepository($config['roleEntity'])
                ->find($this->params()->fromRoute('id'));
        $form->bind($role);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $role = $form->getData();
                $this->getOM()->persist($role);
                $this->getOM()->flush();
                $this->flashMessenger()->addSuccessMessage($translator->translate('Role updated'));
                $this->redirect()->toRoute('zfc-user-crud-role');
            }
        }
        $form->prepare();
        return array(
            'form' => $form
        );
    }

    public function removeAction() {
        $translator = $this->getServiceLocator()->get('translator');
        $config = $this->getServiceLocator()->get('zfcusercrud_options');
        $id = $this->params()->fromRoute('id');
        $entity = $this
                ->getOM()
                ->getRepository($config['roleEntity'])
                ->find($id);
        $this->getOM()->remove($entity);
        $this->getOM()->flush();
        $this->flashMessenger()->addSuccessMessage($translator->translate('Role removed'));
        $this->redirect()->toRoute('zfc-user-crud-role');
    }

    private function getForm() {
        $translator = $this->getServiceLocator()->get('translator');
        $config = $this->getServiceLocator()->get('zfcusercrud_options');
        $role = new $config['roleEntity'];
        $form = new Form('role');
        $form
                ->setAttribute('class', 'form-horizontal')
                ->setHydrator(new DoctrineHydrator($this->getOM()))
                ->setObject($role)
                ->add(array(
                    'name' => 'roleId',
                    'options' => array(
                        'label' => $translator->translate('Role')
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                    )
                ))
                ->add(array(
                    'name' => 'parent',
                    'type' => 'DoctrineModule\Form\Element\ObjectSelect',
                    'options' => array(
                        'label' => $translator->translate('Parent Role'),
                        'object_manager' => $this->getOM(),
                        'target_class' => $config['roleEntity'],
                        'property' => 'roleId',
                        'empty_option' => 'None'
                    ),
                ))
                ->add(array(
                    'name' => 'save',
                    'type' => 'submit',
                    'attributes' => array(
                        'value' => 'Save',
                        'class' => 'btn btn-sm btn-success'
                    )
        ));

        $filter = new InputFilter();
        $filter
                ->add(array(
                    'name' => 'roleId',
                    'required' => true
                ))
                ->add(array(
                    'name' => 'parent',
                    'required' => false
                ))
        ;
        $form->setInputFilter($filter);

        return $form;
    }

}
