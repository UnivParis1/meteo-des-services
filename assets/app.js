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

require('jquery');
require("jquery-datetimepicker/build/jquery.datetimepicker.full");

import DateFormatter from "php-date-formatter/js/php-date-formatter.min";
import { Tooltip } from 'bootstrap';
import { DateTime } from 'luxon';
import { main } from "@popperjs/core";

global.$ = global.jQuery = $;

$(function () {
    const myModalEl = document.getElementById('details');

    if (myModalEl != null) {
        myModalEl.addEventListener('show.bs.modal', event => {

            $("#history #nav-tabContent #nav-maintenances").removeClass('active').addClass('fade');
            $("#history #nav-tabContent #nav-applications").removeClass('fade').addClass('active').addClass('show');
            $('#history nav div#nav-tab button.nav-link').removeClass('active');
            $('#history nav div#nav-tab button#nav-applications-tab').addClass('active');

            let applicationId = event.relatedTarget.attributes['applicationid'].value;

            $.ajax({
                async: false, // obligatoire pour ne pas avoir les champs vides
                url: '/meteo/api/application/' + applicationId, // renvoie le contenu de la pop-up
                method: 'GET',
                success: (response) => successDetail(response),
                error: (xhr, status, error) => console.error(error)
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
        onSelectTime: (ct, target) => $(target).blur()
    });

    // rajoute le click sur l'icone calendar
    $('form i.dtipicker').on('click', (elem) => $(elem.target).prev().datetimepicker('show'));

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
    window.icones = response.icones;
    let size = response.icone[3];
    let application = response.application;
    let icone = response.icone;
    let title = response.application.title;

    $('#details #details-title').html(title);

    let meteoIcon = $('#details #meteo-icon');
    meteoIcon.attr('class', 'm-auto me-3 rounded-circle d-flex align-items-center justify-content-center ' + 'bg-' + icone[2]);

    let svg = meteoIcon.children();
    svg.attr('width', size);
    svg.attr('height', size);
    svg.attr('viewBox', "0 0 " + size + ' ' + size);

    let pathd = svg.children();
    pathd.attr('d', icone[4]);

    $('#details #detail-app-msg').html(application.message);

    $('#details #lastUpdate').html(application.lastUpdate ? formatDateDetails(application.lastUpdate) : '');

    if (response.application.isInMaintenance) {
        buildMaintenanceEnCours(application.nextMaintenance);

        for (let i = 0; i < application.nextMaintenances.length; i++) {
            if (application.nextMaintenances[i].id == application.nextMaintenance.id) {
                application.nextMaintenances.splice(i, 1);
            }
        }
    } else {
        $("#maintenance-en-cours").addClass('d-none');
    }

    buildProchaineMaintenances(application.nextMaintenances);

    if (application.orderedHistosAndMtncs.length > 0) {
        buildApplicationHistory(application.orderedHistosAndMtncs);

        if (application.orderedHistoriqueMtncs.length > 0) {
            $('#history nav div.nav button.nav-link').removeClass('d-none');
            buildMaintenancesHistory(application.orderedHistoriqueMtncs);
        } else {
            $('#history nav div.nav button.nav-link').addClass('d-none');
        }

    } else {
        $("#details #history").addClass('d-none');
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

function buildMaintenanceEnCours(mtnc) {
    let refDom = $("#maintenance-en-cours");
    refDom.removeClass('d-none');

    let tds = refDom.find('td');
    tds[0].textContent = formatDateMtncHisto(mtnc.startingDate);
    tds[1].textContent = mtnc.totalTime;
    tds[2].textContent = window.icones[mtnc.state][0];

    let stateClasses = $('#maintenance-en-cours tr td:last-child').attr('class').split(' ');

    // si le dernier element est de type text-, on sait que c'est une classe utilisé pour l'état d'une application (perturbé, indisponible...)
    if (stateClasses.at(-1).startsWith('text-')) {
        stateClasses.pop();
    }

    stateClasses.push(window.icones[mtnc.state][1]);
    tds[2].classList.value = stateClasses.join(' ');
}

function buildProchaineMaintenances(maintenances) {
    let nomaintenances = $("#details #nomaintenances");
    let tablemtnc = $('#details #nomaintenances + table');

    if (maintenances.length == 0) {
        nomaintenances.removeClass('d-none');
        tablemtnc.addClass('d-none');
    } else {
        nomaintenances.addClass('d-none');
        tablemtnc.removeClass('d-none');

        let stateClasses = $('#details #nomaintenances + table tr td:last-child').attr('class').split(' ');

        if (stateClasses.at(-1).startsWith('text-')) {
            stateClasses.pop();
        }

        let trs = $('#details #nomaintenances + table tr');
        for (let i = 2; i < trs.length; i++) {
            trs[i].remove();
        }

        let tds = $('#details #nomaintenances + table tr td');

        let i = 0;
        do {
            let maintenance = maintenances[i];

            stateClasses.push(window.icones[maintenance.state][1]);

            tds[0].textContent = formatDateMtncHisto(maintenance.startingDate);
            tds[1].textContent = maintenance.totalTime;
            tds[2].textContent = window.icones[maintenance.state][0];

            // équivalent de impode en php ...
            tds[2].classList.value = stateClasses.join(' ');

            i++;
            if (i < maintenances.length) {
                let newTr = trs[1].cloneNode(true);
                $('#details #nomaintenances + table tbody').append(newTr);
                tds = newTr.children;
            }
        } while (i < maintenances.length);
    }
}

function buildApplicationHistory(histories) {
    let historyElem = $("#details #history");
    historyElem.removeClass('d-none');

    let trs = $('#history #nav-tabContent #nav-applications table tbody tr');

    for (let i = 2; i < trs.length; i++) {
        trs[i].remove();
    }

    let tds = $('#history #nav-tabContent #nav-applications table tbody td');
    let i = 0;
    do {
        let history = histories[i];
        tds[0].textContent = formatDateMtncHisto(history.date);
        tds[1].textContent = window.icones[history.state][0];
        tds[2].textContent = history.message;
        tds[3].textContent = history.author;
        tds[4].textContent = history.hasOwnProperty('maintenance_id') ? 'Maintenance' : 'Hors maintenance';

        i++;
        if (i < histories.length) {
            let newTr = trs[1].cloneNode(true);
            $('#history #nav-tabContent #nav-applications table tbody').append(newTr);
            tds = newTr.children;
        }
    } while (i < histories.length);
}

function buildMaintenancesHistory(orderedHistoriqueMtncs) {
    $('#history nav div.nav button.nav-link').removeClass('d-none');

    let trs = $('#history #nav-tabContent #nav-maintenances table tbody tr');

    for (let i = 2; i < trs.length; i++) {
        trs[i].remove();
    }

    let tds = $('#history #nav-tabContent #nav-maintenances table tbody td');
    let i = 0;
    do {
        let maintenance = orderedHistoriqueMtncs[i];

        tds[0].textContent = formatDateMtncHisto(maintenance.date);
        tds[1].textContent = maintenance.type;
        tds[2].textContent = maintenance.state;
        tds[3].textContent = maintenance.author;
        tds[4].textContent = maintenance.message;
        tds[5].textContent = formatDateMtncHisto(maintenance.startingDate);
        tds[6].textContent = formatDateMtncHisto(maintenance.endingDate);

        i++;
        if (i < orderedHistoriqueMtncs.length) {
            let newTr = trs[1].cloneNode(true);
            $('#history #nav-tabContent #nav-maintenances table tbody').append(newTr);
            tds = newTr.children;
        }
    } while (i < orderedHistoriqueMtncs.length);
}

