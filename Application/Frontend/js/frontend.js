/**
 * Tales of Souls - website application
 *
 * Uses jsOMS to send the forms of the website to the API (/api/...) without reloading the page.
 * Every page also works without JavaScript: the API redirects normal form posts back to the website.
 *
 * Forms are handled by jsOMS if they have an id. Forms without id (e.g. search/filter forms) keep the
 * normal browser behavior. Forms with a data-confirm attribute ask before they are sent.
 *
 * The API answers with JSON:
 *  - [{"status": "ok|info|warning|error", "message": "..."}]  -> notification
 *  - [{"type": "redirect", "uri": "/feedback/9"}]                 -> response action (see RESPONSE_EVENTS)
 *
 * @since 1.0.0
 */
import { jsOMS }               from '../../../jsOMS/Utils/oLib.js';
import { Logger }              from '../../../jsOMS/Log/Logger.js';
import { EventManager }        from '../../../jsOMS/Event/EventManager.js';
import { ResponseManager }     from '../../../jsOMS/Message/Response/ResponseManager.js';
import { UIManager }           from '../../../jsOMS/UI/UIManager.js';
import { NotificationManager } from '../../../jsOMS/Message/Notification/NotificationManager.js';
import { NotificationMessage } from '../../../jsOMS/Message/Notification/NotificationMessage.js';
import { NotificationType }    from '../../../jsOMS/Message/Notification/NotificationType.js';
import { NotificationLevel }   from '../../../jsOMS/Message/Notification/NotificationLevel.js';
import { HttpUri }             from '../../../jsOMS/Uri/HttpUri.js';
import { UriFactory }          from '../../../jsOMS/Uri/UriFactory.js';
import { formValidationMessage } from '../../../jsOMS/Model/Message/FormValidation.js';
import { redirectMessage }     from '../../../jsOMS/Model/Action/Dom/Redirect.js';

/**
 * Show a notification
 *
 * @param {string} status  Notification level (ok, info, warning, error)
 * @param {string} message Message
 *
 * @return {void}
 */
function notify (status, message)
{
    if (!message) {
        return;
    }

    const msg    = new NotificationMessage(status || NotificationLevel.INFO, '', message);
    msg.duration = 5000;

    window.omsApp.notifyManager.send(msg, NotificationType.APP_NOTIFICATION);
}

/**
 * Format a score (+12, 0, -3)
 *
 * @param {number} score Score
 *
 * @return {string}
 */
function formatScore (score)
{
    return (score > 0 ? '+' : '') + score;
}

/**
 * Response actions of the API (the "type" of the response)
 */
export const RESPONSE_EVENTS = {
    /**
     * Show a notification
     *
     * @param {{status:string, message:string}} data
     */
    notify: function (data)
    {
        notify(data.status, data.message);
    },

    /**
     * Mark invalid form fields
     */
    validation: formValidationMessage,

    /**
     * Load another page (the message is shown on that page)
     *
     * @param {{uri:string, delay:number, status:string, message:string}} data
     */
    redirect: function (data)
    {
        if (data.delay > 0) {
            notify(data.status, data.message);
        }

        redirectMessage({ uri: data.uri, delay: data.delay || 0 });
    },

    /**
     * Update the votes of a feedback or comment
     *
     * @param {{target:string, vote:string, score:number, up?:number, down?:number}} data
     */
    vote: function (data)
    {
        const box = document.querySelector('[data-vote="' + data.target + '"]');
        if (box === null) {
            return;
        }

        const score = box.querySelector('.vote-score');
        if (score !== null) {
            score.textContent = formatScore(data.score);
            score.classList.toggle('negative', data.score < 0);
        }

        box.querySelectorAll('button').forEach(function (button) {
            button.classList.toggle('voted', button.classList.contains('vote-' + data.vote));
        });

        if (typeof data.up !== 'undefined') {
            const up   = document.querySelector('[data-vote-up="' + data.target + '"]');
            const down = document.querySelector('[data-vote-down="' + data.target + '"]');

            if (up !== null) {
                up.textContent = data.up;
            }

            if (down !== null) {
                down.textContent = data.down;
            }
        }
    },

    /**
     * Remove a deleted comment from the comment list
     *
     * @param {{id:number, count:number, status:string, message:string}} data
     */
    commentDelete: function (data)
    {
        const comment = document.getElementById('comment-' + data.id);
        if (comment !== null) {
            comment.remove();
        }

        document.querySelectorAll('[data-comment-count]').forEach(function (count) {
            count.textContent = data.count;
        });

        const empty = document.querySelector('[data-no-comments]');
        if (empty !== null && data.count === 0) {
            empty.hidden = false;
        }

        notify(data.status, data.message);
    },

    /**
     * Add a new comment to the comment list
     *
     * @param {{id:number, author:string, date:string, text:string, count:number, status:string, message:string}} data
     */
    comment: function (data)
    {
        const list = document.getElementById('comment-list');
        const tpl  = document.getElementById('comment-tpl');

        if (list === null || tpl === null) {
            notify(data.status, data.message);

            return;
        }

        const element = document.importNode(tpl.content, true);
        const item    = element.querySelector('li');

        item.id = 'comment-' + data.id;
        element.querySelector('[data-tpl="author"]').textContent = data.author;
        element.querySelector('[data-tpl="date"]').textContent   = data.date;
        element.querySelector('[data-tpl="text"]').textContent   = data.text;

        const deleteForm = element.querySelector('[data-tpl="delete"]');
        if (deleteForm !== null) {
            deleteForm.id = 'comment-delete-' + data.id;
            deleteForm.querySelector('[name="comment"]').value = data.id;
        }

        list.appendChild(element);

        if (deleteForm !== null) {
            window.omsApp.bindForm(deleteForm.id);
        }

        document.querySelectorAll('[data-comment-count]').forEach(function (count) {
            count.textContent = data.count;
        });

        const empty = document.querySelector('[data-no-comments]');
        if (empty !== null) {
            empty.hidden = true;
        }

        const form = document.getElementById('comment-form');
        if (form !== null) {
            form.reset();
        }

        notify(data.status, data.message);
    },
};

/**
 * Website application
 *
 * @since 1.0.0
 */
export class Application
{
    /**
     * @constructor
     */
    constructor ()
    {
        this.logger   = Logger.getInstance(false, false, false);
        window.logger = this.logger;

        this.eventManager    = new EventManager();
        this.responseManager = new ResponseManager();
        this.notifyManager   = new NotificationManager();
        this.uiManager       = new UIManager(this);
        this.request         = null;

        this.state = {
            hasChanges: false,
        };

        // texts used by jsOMS
        jsOMS.lang = jsOMS.lang || {};
        jsOMS.lang.Info         = jsOMS.lang.Info || 'Info';
        jsOMS.lang.invalid_form = jsOMS.lang.invalid_form || 'Please check your input.';

        for (const key in RESPONSE_EVENTS) {
            if (Object.prototype.hasOwnProperty.call(RESPONSE_EVENTS, key)) {
                this.responseManager.add(key, RESPONSE_EVENTS[key]);
            }
        }

        this.reInit();
        this.bindMessages();
    };

    /**
     * (Re-)bind the page
     *
     * Only the forms are handled by jsOMS, links keep the normal browser behavior.
     *
     * @return {void}
     */
    reInit ()
    {
        this.request = new HttpUri(window.location.href);
        this.request.setRootPath(
            HttpUri.parseUrl(
                typeof document.getElementsByTagName('base')[0] !== 'undefined'
                    ? document.getElementsByTagName('base')[0].href
                    : ''
            ).path
        );

        UriFactory.setupUriBuilder(this.request);

        const formManager = this.uiManager.getFormManager();
        formManager.bind();
        this.uiManager.getActionManager().bind();

        for (const id in formManager.forms) {
            if (Object.prototype.hasOwnProperty.call(formManager.forms, id)) {
                this.injectChecks(formManager.forms[id]);
            }
        }
    };

    /**
     * Bind a form that was added after the page was loaded
     *
     * @param {string} id Form id
     *
     * @return {void}
     */
    bindForm (id)
    {
        const formManager = this.uiManager.getFormManager();
        formManager.bind(id);

        if (typeof formManager.forms[id] !== 'undefined') {
            this.injectChecks(formManager.forms[id]);
        }
    };

    /**
     * jsOMS prevents the normal submit, which also skips the browser validation (required, minlength, ...)
     * and the confirmation of forms with data-confirm
     *
     * @param {FormView} formView Form
     *
     * @return {void}
     */
    injectChecks (formView)
    {
        const self = this;

        formView.injectSubmit(function (form, formId) {
            const element = form.getFormElement();

            if (element !== null && typeof element.reportValidity === 'function' && !element.reportValidity()) {
                return false;
            }

            // e.g. delete buttons: <form data-confirm="Delete this comment?">
            if (element !== null && element.hasAttribute('data-confirm') && !window.confirm(element.getAttribute('data-confirm'))) {
                return false;
            }

            self.eventManager.trigger(formId);
        });
    };

    /**
     * Close buttons and auto hide of the messages rendered by the server (e.g. after a redirect)
     *
     * @return {void}
     */
    bindMessages ()
    {
        document.querySelectorAll('.log-msg.flash').forEach(function (message) {
            const close = document.createElement('button');
            close.type      = 'button';
            close.className = 'close';
            close.setAttribute('aria-label', 'Close');
            close.innerHTML = '&times;';
            close.addEventListener('click', function () {
                message.remove();
            });

            message.insertBefore(close, message.firstChild);

            setTimeout(function () {
                message.remove();
            }, 6000);
        });
    };
};

jsOMS.ready(function ()
{
    'use strict';

    window.omsApp = new Application();
});
