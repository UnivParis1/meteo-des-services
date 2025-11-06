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
// start the Stimulus application
require('./stimulus');

import { Tooltip } from 'bootstrap';
require('jquery');

require("jquery-datetimepicker/build/jquery.datetimepicker.full");

import DateFormatter from "php-date-formatter/js/php-date-formatter";

global.DateFormatter = DateFormatter;

global.$ = global.jQuery = $;

import { DateTime } from 'luxon';
import { app } from './stimulus';
import { main } from '@popperjs/core';
$(function () {
    const myModalEl = document.getElementById('details');

    if (myModalEl != null) {

        myModalEl.addEventListener('show.bs.modal', event => {

            let applicationId = event.relatedTarget.attributes['applicationid'].value;

            $.ajax({
                async: false, // obligatoire pour ne pas avoir les champs vides
                url: '/meteo/api/application/' + applicationId, // renvoie le contenu de la pop-up
                method: 'GET',
                success: function (response) {
                    let size = response.icone[3];
                    let application = response.application;
                    let icone = response.icone;
                    let title = response.application.title;
                    let maintenances = response.application.nextMaintenances;

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

                    let dt = DateTime.fromISO(application.lastUpdate).setLocale('fr');
                    $('#details #lastUpdate').html(dt.toFormat("EEEE d MMMM yyyy \'à\' HH\'H'\mm"));

                    let nomaintenances = $("#details #nomaintenances");

                    let tablemtncs = nomaintenances.next();
                    if (maintenances.length == 0) {
                        nomaintenances.removeClass('d-none');
                        tablemtncs.addClass('d-none');
                    } else {
                        nomaintenances.addClass('d-none');
                        tablemtncs.removeClass('d-none');

                        let trmtnc=tablemtncs.children().children();

                        let refMtnc = trmtnc[1].cloneNode(true);

                        // (ré)initialise le visuel pour les maintenances
                        for (let i = 1; i < trmtnc.length; i++) {
                            trmtnc[i].remove();
                        }

                        let tdsMtnc = refMtnc.children;
                        for (let i = 0; i < maintenances.length; i++) {
                            let maintenance = maintenances[i];

                            let dt = DateTime.fromISO(maintenance.startingDate).setLocale('fr');
                            let startTime = dt.toFormat('dd/MM/y') + ' à ' + dt.toFormat("H") + 'H' + dt.toFormat('m');

                            tdsMtnc[0].textContent = startTime;
                            tdsMtnc[1].textContent = maintenance.totalTime;
                            tdsMtnc[2].textContent = maintenance.state;

                            let trnode = document.createElement('tr');

                            for (let j=0; j < tdsMtnc.length; j++) {
                                trnode.appendChild(tdsMtnc[j].cloneNode(true));
                            }

                            tablemtncs.children().append(trnode);
                        }

                        let histories = application.histories;

                        if (histories.length > 0) {
                            let history = $("#details #history");
                            history.removeClass('d-none');

                            let domHisto = history.next().children().children().next();
                            let refHisto = domHisto[0].cloneNode(true);
                            domHisto[0].remove();

                            for (let i = 0; i < histories.length; i++) {
                                console.log(histories[i]);
                            }

                        }
                    }
                },
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

    $.fn.showDetails = function (applicationId) {
        // Affecte les éléments de la pop
        $.ajax({
            url: '/meteo/application/' + applicationId, // renvoie le contenu de la pop-up
            method: 'GET',
            success: function (response) {
                $('#details-content').html(response);
                $.fn.disableHomepageActions('main-block');
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }

    $.fn.confirmDeletion = function (event, url, entityName) {
        event.preventDefault(); // Empêche la redirection immédiate

        // Affiche la popup de confirmation
        if (confirm("Êtes-vous sûr de vouloir supprimer cette " + entityName + " ?")) {
            window.location.href = url; // Redirige l'utilisateur si l'action est confirmée
        }
    }

    $.fn.hideContent = function (contentId) {
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
    $.fn.disableHomepageActions = function (blockId) {
        const elem = document.getElementById(blockId);

        elem.classList.add("pe-none");
        elem.classList.add("opacity-25")
    }

    $.fn.enableHomepageActions = function (blockId) {
        const elem = document.getElementById(blockId);

        elem.classList.remove("pe-none");
        elem.classList.add("opacity-100");
    }

});
