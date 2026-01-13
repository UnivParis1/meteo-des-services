/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import './styles/app.css';
import './styles/font-import-google.css';
import './styles/font-import-google-2.css';
import './vendor/visavail/visavail.css';

import moment from "./vendor/moment/min/moment-with-locales.min.js";
import './vendor/bootstrap/bootstrap.index.js';
import "./vendor/luxon/luxon.index.js";
import "./vendor/jquery/jquery.index.js";
import "./vendor/d3/d3.index.js";
import "./vendor/visavail/visavail.index.js";

import { Tooltip } from 'bootstrap';
import { DateTime } from 'luxon';
import * as d3 from "d3";
import visavail from 'visavail';

import $ from 'jquery';
window.jQuery = $;
window.moment = moment;
window.d3 = d3;

$(function () {
    let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new Tooltip(tooltipTriggerEl)
    });

    document.getElementById('details').addEventListener('show.bs.modal', event => showBSModal(event) );
});

function showBSModal(event) {
        addSpinner();

        $("#history #nav-tabContent #nav-maintenances").removeClass('active').addClass('fade');
        $("#history #nav-tabContent #nav-applications").removeClass('fade').addClass('active').addClass('show');
        $('#history nav div#nav-tab button.nav-link').removeClass('active');
        $('#history nav div#nav-tab button#nav-applications-tab').addClass('active');
        $('#visavail_graph').empty();

        let applicationId = event.relatedTarget.attributes['applicationid'].value;

        var details_request = $.ajax({
            url: '/meteo/api/application/' + applicationId, // renvoie le contenu de la pop-up
            method: 'GET'
        });

        details_request.always( () => removeSpinner() );
        details_request.done( (response) => showDetail(response) );
        details_request.fail( (xhr, status, error) => console.error(error) );
}

function addSpinner() {
    $('#spinner').removeClass('d-none');
    $(".modal-header").addClass('invisible');
    $(".modal-body").addClass('invisible');
}

function removeSpinner() {
    $("#spinner").addClass('d-none');
    $(".modal-header").removeClass('invisible');
    $(".modal-body").removeClass('invisible');
}

function generateVisavailability() {
    window.moment.locale('FR_fr');

    var dataset = [{
        "measure": "Disponibilité de l'application",
        "data": [
            ["2016-01-01 12:00:00", 1, "2016-01-01 13:00:00"],
            ["2016-01-01 14:22:51", 1, "2016-01-01 16:14:12"],
            ["2016-01-01 19:20:05", 0, "2016-01-01 20:30:00"],
            ["2016-01-01 20:30:00", 1, "2016-01-01 22:00:00"]
        ]
    }];
    // visualisation disponibilités
    var options = {
        id_div_container: "visavail_container",
        id_div_graph: "visavail_graph",
        icon: {
            class_has_data: 'fas fa-fw fa-check',
            class_has_no_data: 'fas fa-fw fa-exclamation-circle'
        },
    };

    if (typeof chart == 'undefined') {
        const test = d3.scaleUtc();
	    var chart = visavail.generate(options, dataset);
    }
}

function showDetail(response) {
    globalThis.icones = response.icones;
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
        $("#maintenance-en-cours").removeClass('d-none');

        let fields = [
            { field: 'startingDate', func: formatDateMtncHisto },
            { field: 'totalTime' },
            [
             { field: 'state', func: (state) => globalThis.icones[state][0] },
             { field: 'state', func: getEtatapplicationClassAndText, args: {to:'classList.value', jq: '#maintenance-en-cours tr td:last-child' } }
            ]
        ];
        buildTablesContent(fields, [application.nextMaintenance], '#maintenance-en-cours table');

        for (let i = 0; i < application.nextMaintenances.length; i++) {
            if (application.nextMaintenances[i].id == application.nextMaintenance.id) {
                application.nextMaintenances.splice(i, 1);
            }
        }
    } else {
        $("#maintenance-en-cours").addClass('d-none');
    }

    buildProchaineMaintenances(application.nextMaintenances);

    // suppression de la liste des maintenances finalement supprimés
    for (let i = 0; i < application.orderedHistosAndMtncs.length; i++) {
        if (application.orderedHistosAndMtncs[i].type == "deletion") {
            application.orderedHistosAndMtncs.splice(i, 1);
        }
    }

    if (application.orderedHistosAndMtncs.length > 0) {
        $("#details #history").removeClass('d-none');
        let fields = [{ field: 'date', func: formatDateMtncHisto },
                      [
                        { field: 'state', func: (state) => globalThis.icones[state][0] },
                        { field: 'state', func: getEtatapplicationClassAndText, args: {to:'classList.value', jq: '#history #nav-tabContent #nav-applications table tbody td:last-child' } }
                      ],
                      { field: 'message' },
                      { field: 'author' },
                      { field: 'maintenance_id', func: function (id) { return typeof id == 'undefined' ? 'Hors Maintenance' : 'Maintenance';}} ];
        buildTablesContent(fields, application.orderedHistosAndMtncs, '#history #nav-tabContent #nav-applications table tbody');

        if (application.orderedHistoriqueMtncs.length > 0) {
            $('#history nav div.nav button.nav-link').removeClass('d-none');
            fields = [{ field: 'date', func: formatDateMtncHisto },
                      { field: 'type', func: (data) => (data=='creation' ? 'Création' : (data=='updating') ? 'Mise à jour' : 'Suppression')  },
                      [{ field: 'state', func: (state) => globalThis.icones[state][0] },
                       { field: 'state', func: getEtatapplicationClassAndText, args: {to:'classList.value', jq: '#history #nav-tabContent #nav-maintenances table tbody td:last-child' } }
                      ],
                      { field: 'author' },
                      { field: 'message' },
                      { field: 'startingDate', func: formatDateMtncHisto },
                      { field: 'endingDate', func: formatDateMtncHisto }];
            buildTablesContent(fields, application.orderedHistoriqueMtncs, '#history #nav-tabContent #nav-maintenances table tbody');
        } else {
            $('#history nav div.nav button.nav-link#nav-maintenances-tab').addClass('d-none');
        }

        const tabEl = document.querySelector('button#nav-availability-tab');
        tabEl.addEventListener('shown.bs.tab', event => {
            $('#visavail_graph').empty();
            generateVisavailability(application);
        })
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

function getEtatapplicationClassAndText(state, jqselect) {
    let stateClasses = $(jqselect).attr('class').split(' ');

    if (stateClasses.at(-1).startsWith('text-')) {
        stateClasses.pop();
    }

    stateClasses.push(globalThis.icones[state][1]);

    return stateClasses.join(' ');
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

        let fields = [
            { field: 'startingDate', func: formatDateMtncHisto },
            { field: 'totalTime' },
            [
             { field: 'state', func: (state) => globalThis.icones[state][0] },
             { field: 'state', func: getEtatapplicationClassAndText, args: {to:'classList.value', jq: '#details #nomaintenances + table tr td:last-child' } }
            ]
        ];
        buildTablesContent(fields, maintenances, '#details #nomaintenances + table');
    }
}

function buildTablesContent(fields, elements, jqbasel) {
    let trs = $(jqbasel + ' tr');

    for (let i = 2; i < trs.length; i++) {
        trs[i].remove();
    }

    let tds = $(jqbasel + ' td');
    let i = 0;
    do {
        let elem = elements[i];

        for (let j = 0; j < fields.length; j++) {
            let prop = fields[j];

            if (Array.isArray(prop)) {
                for (let p of prop) {
                    let valueFromFunc;
                    if (p.hasOwnProperty('args') && p.args.hasOwnProperty('jq')) {
                        valueFromFunc = p['func'](elem[p.field], p.args.jq);
                    } else {
                        valueFromFunc = p['func'](elem[p.field]);
                    }

                    if (p.hasOwnProperty('args') && p.args.hasOwnProperty('to')) {
                        if (p.args.to.includes('.')) {
                            let tos = p.args.to.split('.');
                            tds[j][tos[0]][tos[1]] = valueFromFunc;
                        } else {
                            tds[j][p.args.to] = valueFromFunc;
                        }
                    } else {
                        tds[j].textContent = valueFromFunc;
                    }
                }
            }
            else {
                if (prop.hasOwnProperty('func')) {
                    tds[j].textContent = prop['func'](elem[prop.field]);
                } else {
                    tds[j].textContent = elem[prop.field];
                }
            }
        }

        i++;
        if (i < elements.length) {
            let newTr = trs[1].cloneNode(true);
            $(jqbasel).append(newTr);
            tds = newTr.children;
        }
    } while (i < elements.length);
}
