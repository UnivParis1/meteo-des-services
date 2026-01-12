import './styles/app.css';
import './styles/font-import-google.css';
import './styles/font-import-google-2.css';
import './vendor/jquery-datetimepicker/build/jquery.datetimepicker.min.css';

import './vendor/bootstrap/bootstrap.index.js';
import "./vendor/jquery-datetimepicker/jquery-datetimepicker.index.js";
import "./vendor/jquery/jquery.index.js";

import "./vendor/php-date-formatter/php-date-formatter.index.js";

import DateFormatter from "./vendor/php-date-formatter/php-date-formatter.index.js";

import { Tooltip } from 'bootstrap';

import $ from 'jquery';
window.jQuery = $;

$(function () {
    let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl)
    });

    $.datetimepicker.setLocale("fr");
    let format = 'd/m/Y H:i';
    let dtnow = new Date();
    let minDate = dtnow.getDay() + '/' + dtnow.getMonth() + '/' + dtnow.getFullYear() + ' 00:00';
    let optionsDtpicker = {
        format: format,
        maskFormat: format,
        step: 10,
        mask: true,
        minDate: minDate
    };

    $('input#maintenance_startingDate').datetimepicker({
        ...optionsDtpicker,
        onSelectTime: function (ct) {
            $(this).datetimepicker("hide");

            let jqEnding = $('input#maintenance_endingDate');

            let ctstep = new Date(ct);
            ctstep.setMinutes(ct.getMinutes() + optionsDtpicker.step);

            let fmt = new DateFormatter();
            jqEnding.val(fmt.formatDate(ctstep, format));
            jqEnding.focus();
        }
    });

    $('input#maintenance_endingDate').datetimepicker({
        ...optionsDtpicker,
        onSelectTime: (ct, target) => $(target).blur()
    });

    // rajoute le click sur l'icone calendar
    $('form i.dtipicker').on('click', (elem) => $(elem.target).prev().datetimepicker('show'));

    $.fn.confirmDeletion = function (event, url, entityName) {
        event.preventDefault(); // Empêche la redirection immédiate

        // Affiche la popup de confirmation
        if (confirm("Êtes-vous sûr de vouloir supprimer cette " + entityName + " ?")) {
            window.location.href = url; // Redirige l'utilisateur si l'action est confirmée
        }
    }
});
