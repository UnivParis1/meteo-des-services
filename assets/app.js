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

import { Tooltip  } from 'bootstrap';
import $ from 'jquery';

global.$ = global.jQuery = $;

$(function() {
    // Ajout du Tooltip Bootstrap
    let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl)
    });

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

        // Affiche la popup de confirmation
        if (confirm("Êtes-vous sûr de vouloir supprimer cette " + entityName + " ?")) {
            window.location.href = url; // Redirige l'utilisateur si l'action est confirmée
        }
    }

    $.fn.hideContent = function(contentId) {
        const content = document.getElementById(contentId);
        const classes = content.classList.toString();

        // regex cherchant tous les d-* (d-inline ou d-block par ex)
        const regex = /\bd-[a-z]+/gi;
        const matches = classes.match(regex);

        if (matches != null)
            for (const idx in matches)
                content.classList.remove(matches[idx]);

        content.classList.add('d-none');
        $.fn.enableHomepageActions("main-block");
    }

    // ACTIONS SUR LA PAGE D'ACCUEIL
    $.fn.disableHomepageActions = function(blockId)
    {
        const elem = document.getElementById(blockId);

        elem.classList.add("pe-none");
        elem.classList.add("opacity-25")
    }

    $.fn.enableHomepageActions = function(blockId)
    {
        const elem = document.getElementById(blockId);

        elem.classList.remove("pe-none");
        elem.classList.add("opacity-100");
    }

});
