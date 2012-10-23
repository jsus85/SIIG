<?php

namespace MINSAL\IndicadoresBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;

class OrigenDatoAdmin extends Admin {
    /* protected $datagridValues = array(
      '_page' => 1, // Display the first page (default = 1)
      '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
      '_sort_by' => 'nombreBaseDatos' // name of the ordered field (default = the model id field, if any)
      ); */

    protected function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->add('nombre', null, array('label' => $this->getTranslator()->trans('nombre')))
                ->add('descripcion', null, array('label' => $this->getTranslator()->trans('descripcion'), 'required' => false))
                ->add('sentenciaSql', null, array('label' => $this->getTranslator()->trans('sentencia_sql'), 'required' => false))
                ->add('archivo', 'file', array('label' => $this->getTranslator()->trans('archivo'), 'required' => false))
        ;
    }

    /* protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
      $datagridMapper
      ->add('nombreConexion', null, array('label' => $this->getTranslator()->trans('nombre_conexion')))
      ->add('idMotor', null, array('label' => $this->getTranslator()->trans('motor')))
      ;
      } */

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('nombre', null, array('label' => $this->getTranslator()->trans('nombre')))
        ;
    }

    public function validate(ErrorElement $errorElement, $object) {
        if ($object->getArchivo()=='' and $object->getSentenciaSql() == '') {
            $errorElement->with('value')
                    ->addViolation('Debe ingresar una de las dos opciones: Una sentencia SQL o un archivo')
                    ->end();
        }
        if ($object->getArchivo()!='' and $object->getSentenciaSql() != '') {
            $errorElement->with('value')
                    ->addViolation('Solo puede ingresar una de las dos opciones: Una sentencia SQL o un archivo. No ambas')
                    ->end();
        }
        
         new \Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator($registry)
    }

    public function getBatchActions() {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }

    public function getTemplate($name) {
        switch ($name) {
            case 'edit':
                return 'IndicadoresBundle:CRUD:origen_dato-edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

}

?>