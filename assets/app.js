/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

require('./styles/global.scss');

require('./styles/app.css');

require('./styles/font-import-google.css');
require('./styles/font-import-google-2.css');
require("jquery-datetimepicker/jquery.datetimepicker.css");
require('bootstrap-icons/font/bootstrap-icons.min.css');
// start the Stimulus application
require('./stimulus');

require('bootstrap');
require('jquery');

require("jquery-datetimepicker/build/jquery.datetimepicker.full");

import DateFormatter from "php-date-formatter/js/php-date-formatter";

global.DateFormatter = DateFormatter;

global.$ = global.jQuery = $;

$(function() {
    $.datetimepicker.setLocale("fr");

    let optionsDtpicker = {
        format:'d/m/Y H:i',
        step: 10,
        mask: true
    };

    // le datetimepicker est mis sur les inputs html
    $('input#maintenance_startingDate').datetimepicker({
        ...optionsDtpicker,
        onSelectTime: function(ct) {
            let fmt = new DateFormatter();
            let jqEnding = $('input#maintenance_endingDate');

            jqEnding.datetimepicker('setOptions', {
                minDate: fmt.formatDate(ct, 'd/m/Y')
            });

            let ctstep = new Date(ct);
            ctstep.setMinutes(ct.getMinutes() + optionsDtpicker.step);

            jqEnding.val(fmt.formatDate(ctstep, 'd/m/Y H:i')).focus();

            $(this).datetimepicker("hide");
        }
    })

    $('input#maintenance_endingDate').datetimepicker({
        ...optionsDtpicker,
        onSelectTime: function(ct, target) {
            $(target).blur();
        }
    });

    // rajoute le click sur l'icone calendar
    $('.datetimepicker').on('click', function(elem) {
        $(elem.target).prev().datetimepicker('show');
    });

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
