/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
require('./styles/global.scss');
require('./styles/custom_bootstrap.scss');
require('./styles/app.css');
require('./styles/font-import-google.css');
require('./styles/font-import-google-2.css');
require("jquery-datetimepicker/jquery.datetimepicker.css");
require('bootstrap-icons/font/bootstrap-icons.min.css');

require('./stimulus');
require('jquery');
require("jquery-datetimepicker/build/jquery.datetimepicker.full");

import { Tooltip } from 'bootstrap';
import { DateTime } from 'luxon';
import { DateFormatter } from "php-date-formatter/js/php-date-formatter";

global.DateFormatter = DateFormatter;

global.$ = global.jQuery = $;

$(function () {
    const myModalEl = document.getElementById('details');

    if (myModalEl != null) {

        myModalEl.addEventListener('show.bs.modal', event => {

            let applicationId = event.relatedTarget.attributes['applicationid'].value;

            $.ajax({
                async: false, // obligatoire pour ne pas avoir les champs vides
                url: '/meteo/api/application/' + applicationId, // renvoie le contenu de la pop-up
                method: 'GET',
                success: (response) => successDetail(response),
                error: function (xhr, status, error) {
                    console.error(error);
                }
            })
        });
    }

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

    // le datetimepicker est mis sur les inputs html
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
        onSelectTime: function (ct, target) {
            $(target).blur();
        }
    });

    // rajoute le click sur l'icone calendar
    $('false.dtipicker').on('click', function (elem) {
        $(elem.target).prev().datetimepicker('show');
    });

    // Ajout du Tooltip Bootstrap
    let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl)
    });

    $.fn.confirmDeletion = function (event, url, entityName) {
        event.preventDefault(); // Empêche la redirection immédiate

        // Affiche la popup de confirmation
        if (confirm("Êtes-vous sûr de vouloir supprimer cette " + entityName + " ?")) {
            window.location.href = url; // Redirige l'utilisateur si l'action est confirmée
        }
    }
});

function successDetail(response) {
    let size = response.icone[3];
    let application = response.application;
    let icone = response.icone;
    let title = response.application.title;

    $('#details #details-title').html(title);

    let meteoIcon = $('#details #meteo-icon');
    meteoIcon.attr('class', 'm-auto me-3 rounded-circle d-flex align-items-center justify-content-center ' + icone[2]);

    let svg = meteoIcon.children();
    svg.attr('width', size);
    svg.attr('height', size);
    svg.attr('viewBox', "0 0 " + size + ' ' + size);

    let pathd = svg.children();
    pathd.attr('d', icone[4]);

    $('#details #detail-app-msg').html(application.message);

    $('#details #lastUpdate').html(formatDateDetails(application.lastUpdate));

    buildDetailsMaintenance(application.nextMaintenances);

    if (application.histories.length > 0) {
        buildHistories(application.histories);
    }
}

function formatDateDetails(date) {
    let dt = DateTime.fromISO(date).setLocale('fr');
    return dt.toFormat("EEEE d MMMM yyyy \'à\' HH\'H'\mm");
}

function formatDateMtncHisto(date) {
    let dt = DateTime.fromISO(date).setLocale('fr');
    return dt.toFormat('dd/MM/y') + ' à ' + dt.toFormat("HH") + 'H' + dt.toFormat('mm');
}

function buildDetailsMaintenance(maintenances) {
    let nomaintenances = $("#details #nomaintenances");

    let tablemtncs = nomaintenances.next();
    if (maintenances.length == 0) {
        nomaintenances.removeClass('d-none');
        tablemtncs.addClass('d-none');
    } else {
        nomaintenances.addClass('d-none');
        tablemtncs.removeClass('d-none');

        let trmtnc = tablemtncs.find('tbody').children().slice(1);
        let refMtnc = trmtnc[0].cloneNode(true);

        trmtnc.each(function () {
            this.remove();
        });
        // (ré)initialise le visuel pour les maintenances

        let tdsMtnc = refMtnc.children;
        for (let i = 0; i < maintenances.length; i++) {
            let maintenance = maintenances[i];

            tdsMtnc[0].textContent = formatDateMtncHisto(maintenance.startingDate);
            tdsMtnc[1].textContent = maintenance.totalTime;
            tdsMtnc[2].textContent = maintenance.state;

            let trnode = document.createElement('tr');

            for (let j = 0; j < tdsMtnc.length; j++) {
                trnode.appendChild(tdsMtnc[j].cloneNode(true));
            }

            tablemtncs.children().append(trnode);
        }
    }
}

function buildHistories(histories) {
    let history = $("#details #history");
    history.removeClass('d-none');

    let tbodyHistory = history.next().find("tbody");
    let trHistories = tbodyHistory.children().slice(1);

    let trHistory = trHistories[0];

    let trClasses = trHistory.className;
    let trHistoryRef = trHistory.cloneNode(true);
    let firstTrTds = trHistoryRef.children;

    trHistories.each(function () {
        this.remove();
    });

    for (let i = 0; i < histories.length; i++) {
        let history = histories[i];

        firstTrTds[0].textContent = formatDateMtncHisto(history.date);
        firstTrTds[1].textContent = history.state;
        firstTrTds[2].textContent = history.message;
        firstTrTds[3].textContent = history.author;

        let trnode = document.createElement('tr');
        trnode.className = trClasses;

        for (let j = 0; j < firstTrTds.length; j++) {
            trnode.appendChild(firstTrTds[j].cloneNode(true));
        }
        tbodyHistory.append(trnode);
    }
}

