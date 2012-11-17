<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class IndicadorController extends Controller {

    /**
     * @Route("/indicador/dimensiones/{id}", name="indicador_dimensiones", options={"expose"=true})
     */
    public function getDimensiones($id) {

        $resp = array();
        $em = $this->getDoctrine()->getEntityManager();

        $fichaTec = $em->find('IndicadoresBundle:FichaTecnica', $id);
        if ($fichaTec) {
            $resp['nombre_indicador'] = $fichaTec->getNombre();
            $resp['id_indicador'] = $id;
            $campos = explode(',', str_replace("'", '', $fichaTec->getCamposIndicador()));

            foreach ($campos as $campo) {
                $significado = $em->getRepository('IndicadoresBundle:SignificadoCampo')
                        ->findOneByCodigo($campo);
                $dimensiones[$significado->getCodigo()] = $significado->getDescripcion();
            }
            $resp['dimensiones'] = $dimensiones;
            $resp['resultado'] = 'ok';
        }
        else
            $resp['resultado'] = 'error';
        $response = new Response(json_encode($resp));
        $response->setMaxAge(600);
        return $response;
    }

    /**
     * @Route("/indicador/datos/{id}/{dimension}", name="indicador_datos", options={"expose"=true})
     */
    public function getDatos($id, $dimension) {

        $resp = array();
        $filtro = $this->getRequest()->get('filtro');
        if ($filtro == null or $filtro == '')
            $filtros = null;
        else {

            $filtrObj = json_decode($filtro);
            foreach ($filtrObj as $f) {
                $filtros_dimensiones[] = $f->codigo;
                $filtros_valores[] = $f->valor;
            }
            $filtros = array_combine($filtros_dimensiones, $filtros_valores);
        }


        $em = $this->getDoctrine()->getEntityManager();

        $fichaTec = $em->find('IndicadoresBundle:FichaTecnica', $id);
        $fichaRepository = $em->getRepository('IndicadoresBundle:FichaTecnica');


        $fichaRepository->crearTablaIndicador($fichaTec);
        $resp['datos'] = $fichaRepository->calcularIndicador($fichaTec, $dimension, $filtros);
        $response = new Response(json_encode($resp));
        $response->setMaxAge(600);
        return $response;
    }

    /**
     * @Route("/indicador/datos/ordenar", name="indicador_datos_ordenar", options={"expose"=true})
     */
    public function getDatosOrdenados() {
        $ordenar_por = $this->getRequest()->get('ordenar_por');
        $modo = $this->getRequest()->get('modo');
        $datos = $this->getRequest()->get('datos');

        // Adecuar el arreglo para luego ordenarlo
        $datos_aux = array();
        foreach ($datos as $fila) {
            $datos_aux[$fila['category']] = $fila['measure'];
        }
        if ($ordenar_por == 'dimension') {
            if ($modo == 'desc')
                krsort($datos_aux);
            else
                ksort($datos_aux);
        }
        else {
            if ($modo == 'desc')
                arsort($datos_aux);
            else
                asort($datos_aux);
        }
        
        $datos_ordenados = array();        
        $i=0;
        
        foreach($datos_aux as $k=>$medida){
            $datos_ordenados[$i]['category'] = $k;
            $datos_ordenados[$i]['measure'] = $medida;
            $i++;
        }
        $resp['datos'] = $datos_ordenados;
        
        $response = new Response(json_encode($resp));
        //$response->setMaxAge(600);
        return $response;
    }

}
