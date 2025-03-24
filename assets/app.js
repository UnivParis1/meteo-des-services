/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

import './styles/global.scss';

import './styles/app.css';

import './styles/font-import-google.css';
import './styles/font-import-google-2.css';
// start the Stimulus application
import './bootstrap';

import $ from 'jquery';

global.$ = global.jQuery = $;

$(function() {

    $.fn.showDetails = function(applicationId) {
        // Affecte les éléments de la pop
        $.ajax({
            url: '/meteo/application/' + applicationId, // renvoie le contenu de la pop-up
            method: 'GET',
            success: function(response) {
                $('#details-content').html(response);
                $.fn.disableHomepageActions('main-block');
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    $.fn.confirmDeletion = function(event, url, entityName) {
        event.preventDefault(); // Empêche la redirection immédiate

        console.log("work");
        // Affiche la popup de confirmation
        if (confirm("Êtes-vous sûr de vouloir supprimer cette " + entityName + " ?")) {
            window.location.href = url; // Redirige l'utilisateur si l'action est confirmée
        }
    }

    $.fn.hideContent = function(contentId) {
        document.getElementById(contentId).style.display = "none";
        $.fn.enableHomepageActions("main-block");
    }

    // ACTIONS SUR LA PAGE D'ACCUEIL
    $.fn.disableHomepageActions = function(blockId)
    {
        document.getElementById(blockId).style.pointerEvents = "none";
        document.getElementById(blockId).style.opacity = "0.25";
    }

    $.fn.enableHomepageActions = function(blockId)
    {
        document.getElementById(blockId).style.pointerEvents = "auto";
        document.getElementById(blockId).style.opacity = "1";
    }
    });