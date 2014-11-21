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

class CrudController extends AbstractActionController {

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

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
                ->getRepository($config['userEntity'])
                ->createQueryBuilder('q');
        $searchTerm = '';
        if ($this->getRequest()->isPost()) {
            $searchTerm = $this->params()->fromPost('searchTerm');
            $query
                    ->where('q.username LIKE :search1')
                    ->orWhere('q.email LIKE :search2')
                    ->orWhere('q.displayName LIKE :search3')
                    ->setParameter('search1', "%{$searchTerm}%")
                    ->setParameter('search2', "%{$searchTerm}%")
                    ->setParameter('search3', "%{$searchTerm}%")
            ;
        }
        $paginator = new Paginator(
                new DoctrinePaginator(new ORMPaginator($query))
        );
        $paginator
                ->setCurrentPageNumber($this->params()->fromQuery('page', 1))
                ->setItemCountPerPage(20);
        return array(
            'users' => $paginator,
            'searchTerm' => $searchTerm
        );
    }

    public function newAction() {
        $translator = $this->getServiceLocator()->get('translator');
        $form = $this->getForm();
        $filter = $form->getInputFilter();
        $filter
                ->add(array(
                    'name' => 'password',
                    'required' => true
                ))
                ->add(array(
                    'name' => 'password_confirm',
                    'required' => true
                ))
        ;
        $form->setInputFilter($filter);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $user = $form->getData();
                $user->setPassword($this->encriptPassword($user->getPassword()));
                $this->getOM()->persist($user);
                $this->getOM()->flush();
                $this->flashMessenger()->addSuccessMessage($translator->translate('User saved'));
                $this->redirect()->toRoute('zfc-user-crud');
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
        $user = $this
                ->getOM()
                ->getRepository($config['userEntity'])
                ->find($this->params()->fromRoute('id'));
        $currentPassword = $user->getPassword();
        $form->bind($user);
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $user = $form->getData();
                /* @var $user User */
                if ($user->getPassword() != '') {
                    $user->setPassword($this->encriptPassword($user->getPassword()));
                } else {
                    $user->setPassword($currentPassword);
                }
                $this->getOM()->persist($user);
                $this->getOM()->flush();
                $this->flashMessenger()->addSuccessMessage($translator->translate('User updated'));
                $this->redirect()->toRoute('zfc-user-crud');
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
                ->getRepository($config['userEntity'])
                ->find($id);
        $this->getOM()->remove($entity);
        $this->getOM()->flush();
        $this->flashMessenger()->addSuccessMessage($translator->translate('User removed'));
        $this->redirect()->toRoute('zfc-user-crud');
    }

    public function encriptPassword($newPass) {
        $bcrypt = new Bcrypt;
        $bcrypt->setCost($this->getOptions()->getPasswordCost());
        $pass = $bcrypt->create($newPass);
        return $pass;
    }

    protected function getForm() {
        $translator = $this->getServiceLocator()->get('translator');
        $config = $this->getServiceLocator()->get('zfcusercrud_options');
        $user = new $config['userEntity'];
        $form = new Form('user');
        $form
                ->setAttribute('class', 'form-horizontal')
                ->setHydrator(new DoctrineHydrator($this->getOM()))
                ->setObject($user)
                ->add(array(
                    'name' => 'displayName',
                    'options' => array(
                        'label' => $translator->translate('Name')
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                    )
                ))
                ->add(array(
                    'name' => 'username',
                    'options' => array(
                        'label' => $translator->translate('Username')
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                    )
                ))
                ->add(array(
                    'name' => 'email',
                    'type' => 'email',
                    'options' => array(
                        'label' => $translator->translate('Email')
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                    )
                ))
                ->add(array(
                    'name' => 'password',
                    'type' => 'password',
                    'options' => array(
                        'label' => $translator->translate('Password')
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                    )
                ))
                ->add(array(
                    'name' => 'password_confirm',
                    'type' => 'password',
                    'options' => array(
                        'label' => $translator->translate('Password Confirm')
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                    )
                ))
                ->add(array(
                    'name' => 'state',
                    'type' => 'checkbox',
                    'options' => array(
                        'label' => $translator->translate('Enabled')
                    )
                ))
                ->add(array(
                    'name' => 'roles',
                    'type' => 'DoctrineModule\Form\Element\ObjectMultiCheckbox',
                    'options' => array(
                        'label' => $translator->translate('Roles'),
                        'object_manager' => $this->getOM(),
                        'target_class' => $config['roleEntity'],
                        'property' => 'roleId'
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
                    'name' => 'username',
                    'required' => true
                ))
                ->add(array(
                    'name' => 'displayName',
                    'required' => true
                ))
                ->add(array(
                    'name' => 'email',
                    'required' => true,
                    'validators' => array(
                        array(
                            'name' => 'EmailAddress'
                        )
                    )
                ))
                ->add(array(
                    'name' => 'password_confirm',
                    'required' => false,
                    'validators' => array(
                        array(
                            'name' => 'Identical',
                            'options' => array(
                                'token' => 'password',
                            )
                        )
                    )
                        )
                )
        ;
        $form->setInputFilter($filter);

        return $form;
    }
    
    protected function getPasswordForm() {
        $translator = $this->getServiceLocator()->get('translator');
        $form = new Form('password');
        $form
                ->setAttribute('class', 'form-horizontal')
                ->add(array(
                    'name' => 'oldPassword',
                    'type' => 'password',
                    'options' => array(
                        'label' => $translator->translate('Old password')
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                    )
                ))
                ->add(array(
                    'name' => 'password',
                    'type' => 'password',
                    'options' => array(
                        'label' => $translator->translate('New password')
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                    )
                ))
                ->add(array(
                    'name' => 'password_confirm',
                    'type' => 'password',
                    'options' => array(
                        'label' => $translator->translate('Confirm password')
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm',
                    )
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
                    'name' => 'password',
                    'required' => true
                ))
                ->add(array(
                    'name' => 'oldPassword',
                    'required' => true
                ))
                ->add(array(
                    'name' => 'password_confirm',
                    'required' => false,
                    'validators' => array(
                        array(
                            'name' => 'Identical',
                            'options' => array(
                                'token' => 'password',
                            )
                        )
                    )
                        )
                )
        ;
        $form->setInputFilter($filter);

        return $form;
    }
    
    public function passwordAction(){
        $translator = $this->getServiceLocator()->get('translator');
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            $this->flashMessenger()->addWarningMessage($translator->translate('User not logged in'));
            $this->redirect()->toRoute('home');
            return true;
        }
        $form = $this->getPasswordForm();
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $user = $this->zfcUserAuthentication()->getIdentity();
                $bcrypt = new Bcrypt;
                $bcrypt->setCost($this->getOptions()->getPasswordCost());
                if (!$bcrypt->verify($data['oldPassword'], $user->getPassword())) {
                    $this->flashMessenger()->addErrorMessage($translator->translate('Old password don\'t match'));
                    $this->redirect()->toRoute('zfc-user-crud-password');
                    return false;
                }
                $user->setPassword($this->encriptPassword($data['password']));
                $this->getOM()->persist($user);
                $this->getOM()->flush();
                $this->flashMessenger()->addSuccessMessage($translator->translate('Password changed'));
                $this->redirect()->toRoute('zfc-user-crud-password');
            }
        }
        $form->prepare();
        return array(
            'form' => $form
        );
    }

    /**
     * get service options
     *
     * @return UserServiceOptionsInterface
     */
    public function getOptions() {
        if (!$this->options instanceof UserServiceOptionsInterface) {
            $this->setOptions($this->getServiceLocator()->get('zfcuser_module_options'));
        }
        return $this->options;
    }

    /**
     * set service options
     *
     * @param UserServiceOptionsInterface $options
     */
    public function setOptions(UserServiceOptionsInterface $options) {
        $this->options = $options;
    }

}
