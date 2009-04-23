/**
 * Copyright (C) 2009 by Trever Fischer
 * wm161@wm161.net
 *
 * This file is part of GuestTracker.
 * 
 * GuestTracker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * GuestTracker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with GuestTracker. If not, see <http://www.gnu.org/licenses/>
 */

Event.observe(document, 'dom:loaded', function() {
    $$('.sign-in').each(function(e) {
        new CheckinScanner(e);
    });
});

CheckinScanner = Class.create({
    initialize: function(scanner) {
        this.scanner = $(scanner)
        this.residentInput = scanner.down('.resident-input');
        this.guestInput = scanner.down('.guest-input');
        this.display = new Element('div', {class:'display'});

        Event.observe(this.residentInput, 'keypress', this.captureResidentEnter.bindAsEventListener(this));
        Event.observe(this.guestInput, 'keypress', this.captureGuestEnter.bindAsEventListener(this));
    },

    captureResidentEnter: function(event) {
        if (event.keyCode == 13) {
            Event.stop(event);
            this.processResident();
        }
    },

    captureGuestEnter: function(event) {
        if (event.keyCode == 13) {
            Event.stop(event);
            this.processGuest();
        }
    },

    processResident: function() {
        this.residentInput.removeClassName('error');
        params = { };
        params['data[Resident][card_num]'] = $F(this.residentInput);
        new Ajax.Request('/residents/search.json', {
            parameters: params,
            onSuccess: (function(response) {
                var data = response.headerJSON;
                this.setResident(data);
            }).bind(this),
            onFailure: (function(response) {
                this.showError(response);
            }).bind(this)
        });
    },

    processGuest: function() {
        params = { };
        params['data[Identification][card_num]'] = $F(this.guestInput);
        new Ajax.Request('/identifications/search.json', {
            parameters: params,
            onSuccess: (function(response) {
                var data = repsonse.headerJSON;
                this.addGuest(data);
            }).bind(this),
            onFailure: (function(response) {
                this.showError(response);
            }).bind(this)
        });
    },

    setResident: function(data) {
        if (data) {
            this.error.hide();
            this.residentInput.replace(this.residentDisplay);
            this.value.value = data.Identification.card_num;
            this.display.update(data.Person.lastName+', '+data.Person.firstName);
            Event.observe(this.residentDisplay, 'click', this.editResident.bind(this));
        } else {
            //this.error("
        }
    }
});