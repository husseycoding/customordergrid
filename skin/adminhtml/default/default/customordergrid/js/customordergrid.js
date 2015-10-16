var selected = Class.create({
    initialize: function() {
        $("row_customordergrid_configure_columnsorder").hide();
        $("row_customordergrid_configure_columnswidth").hide();
        if ($("customordergrid_configure_columnsorder").value) {
            this.userselected = $("customordergrid_configure_columnsorder").value.split(",");
        } else {
            this.userselected = new Array();
        }
        this.selectedwidths = {};
        this.getWidthArray();
        this.getText();
        Event.observe($("customordergrid_configure_columns"), "change", this.updateForm.bindAsEventListener(this));
        $("row_customordergrid_configure_columns").insert({ after: "<tr><td></td><td class=\"value\"><div style=\"width:240px; display:inline-block\"><span style=\"font-weight:bold\">Column</span> - drag to order</div><div style=\"display:inline-block; font-weight:bold\">Width</div></td></tr><tr><td class=\"label\">Column Display Order</td><td id=\"displaycolumnorder\" class=\"value\"></td></tr>" });
        this.outputOrder();
        this.updateSortElement();
        this.observeWidthInput();
    },
    addColumn: function(id) {
        if (this.userselected.indexOf(id) == -1) {
            this.userselected.push(id);
        }
        if (!this.selectedwidths[id]) {
            this.selectedwidths[id] = "";
        }
    },
    removeColumn: function(id) {
        var count = 0;
        this.userselected.each(function(s) {
            if (s == id) {
                this.userselected.splice(count, 1);
                throw $break;
            }
            count++;
        }.bind(this));
    },
    updateForm: function() {
        this.checkForm();
        this.outputOrder();
        this.updateSortElement();
        this.observeWidthInput();
    },
    checkForm: function() {
        var selectedoptions = {};
        this.userselected.each(function(s) {
            selectedoptions[s] = true;
        }.bind(this));
        for (var i = 0; i < $("customordergrid_configure_columns").options.length; i++) {
            var value = $("customordergrid_configure_columns").options[i].value;
            if (selectedoptions[value] && !$("customordergrid_configure_columns").options[i].selected) {
                this.removeColumn(value);
            } else if (!selectedoptions[value] && $("customordergrid_configure_columns").options[i].selected) {
                this.addColumn(value);
            }
        }
    },
    getText: function() {
        this.valuetext = {};
        this.reversevaluetext = {};
        for (var i = 0; i < $("customordergrid_configure_columns").options.length; i++) {
            this.valuetext[$("customordergrid_configure_columns").options[i].value] = $("customordergrid_configure_columns").options[i].text;
            this.reversevaluetext[$("customordergrid_configure_columns").options[i].text] = $("customordergrid_configure_columns").options[i].value;
        }
    },
    outputOrder: function() {
        var html = new Array();
        var hidden = new Array();
        this.userselected.each(function(s) {
            if (this.valuetext[s]) {
                var widthvalue = !this.selectedwidths[s] ? "" : this.selectedwidths[s];
                html.push("<div class=\"sortables\"><li class=\"sortablevalues\" style=\"width:240px; display:inline-block; cursor:grab\">" + this.valuetext[s] + "</li><input class=\"widthinput\" type=\"text\" name=\"" + this.reversevaluetext[this.valuetext[s]] + "\" value=\"" + widthvalue + "\" style=\"display:inline-block; width:34px\"></div>");
                hidden.push(s);
            }
        }.bind(this));
        hidden = hidden.join(",");
        html = html.join("");
        $("customordergrid_configure_columnsorder").value = hidden;
        $("displaycolumnorder").update(html);
        this.createSortable();
    },
    updateSortElement: function() {
        for (var i = 0; i < $("customordergrid_configure_columnsort").options.length; i++) {
            var el = $("customordergrid_configure_columnsort").options[i];
            if (this.userselected.indexOf(el.value) >= 0 && el.value != "tracking_number") {
                el.disabled = false;
            } else if (el.value && el.value != "real_order_id") {
                if (el.selected) {
                    el.selected = false;
                    $("customordergrid_configure_columnsort").options[0].selected  = true;
                }
                el.disabled = true;
            }
        }
    },
    createSortable: function() {
        Sortable.create("displaycolumnorder", {
                tag:"div",
                onChange: function() {
                    var widths = new Array();
                    var columns = new Array();
                    $$("input.widthinput").each(function(e) {
                        widths.push(e.name + ":" + e.value);
                        columns.push(e.name);
                    }.bind(this));
                    widths = widths.join(",");
                    columns = columns.join(",");
                    $("customordergrid_configure_columnswidth").value = widths;
                    $("customordergrid_configure_columnsorder").value = columns;
                    $("customordergrid_configure_columns").value = columns;
                    
                }
            }
        );
    },
    observeWidthInput: function() {
        $$("input.widthinput").each(function(e) {
            e.observe("change", function() {
                var string = new Array();
                $$("input.widthinput").each(function(el) {
                    string.push(el.name + ":" + el.value);
                }.bind(this));
                string = string.join(",");
                $("customordergrid_configure_columnswidth").value = string;
            }.bind(this));
        }.bind(this));
    },
    getWidthArray: function() {
        if ($("customordergrid_configure_columnswidth").value) {
            var splitwidths = $("customordergrid_configure_columnswidth").value.split(",");
            splitwidths.each(function(e) {
                var splitwidth = e.split(":");
                var column = splitwidth.shift();
                var width = splitwidth.shift();
                this.selectedwidths[column] = width;
            }.bind(this));
        }
    }
});

document.observe("dom:loaded", function() {
    if ($("customordergrid_configure_columnsorder")) {
        var selecteditems = new selected();
    }
});