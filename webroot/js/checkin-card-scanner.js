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