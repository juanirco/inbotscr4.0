<?php

namespace Controllers;

use MVC\Router;

class PagesController {
    public static function inicio(Router $router){
        $router->render('pages/inicio',[
            'title' => 'Inicio',
            'description' => 'Expertos en Smartbots con IA. Revoluciona la comunicación de tu negocio con bots impulsados por inteligencia artificial.'
        ]);
    }

    public static function nosotros(Router $router){
        $router->render('pages/nosotros',[
            'title' => 'Nosotros',
            'description' => 'Conoce nuestra historia de innovación en smartbots e IA. Desde 2018 transformando la comunicación empresarial.'
        ]);
    }

    public static function smartbots(Router $router){
        $router->render('pages/smartbots',[
            'title' => 'Smartbots',
            'description' => 'Descubre el futuro de la comunicación. Smartbots que convierten cada consulta en oportunidad de venta 24/7.'
        ]);
    }

    public static function contacto(Router $router){
        $router->render('pages/contacto',[
            'title' => 'Contacto',
            'description' => 'Contáctanos para implementar smartbots en tu negocio. Respuesta inmediata via WhatsApp, Messenger o Instagram.'
        ]);
    }

    public static function privacidad(Router $router){
        $router->render('pages/privacidad',[
            'title' => 'Políticas de Privacidad',
            'description' => 'Políticas de privacidad de Inbotscr. Conoce como protegemos tus datos.'
        ]);
    }

    public static function condiciones(Router $router){
        $router->render('pages/condiciones',[
            'title' => 'Términos y Condiciones',
            'description' => 'Términos y condiciones de servicio de Inbotscr.'
        ]);
    }
}