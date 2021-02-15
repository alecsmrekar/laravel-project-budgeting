<script>
export default {
    name: 'NewCostItem',
    mounted() {
        this.loadCostItem();
    },
    props: {
        modal_id: {
            type: Number,
            default: -1,
        },
        project_id: {
            type: Number,
            required: true,
        },
        all_costs : {
            type: Array,
        },
    },
    data: function () {
        return {
            department: '',
            sector: '',
            service: 'test',
            person: '',
            company: '',
            manual_actuals: 0,
            tag: '',
            budget: 1,
            tax_rate: 0.5,
            final: 0,
            comment: '',
            errors: [],
        }
    },
    methods: {
        // Called on mounted: if modal_id is predetermined, load it
        loadCostItem() {
            if (this.modal_id !== -1) {
                axios.get('/api/costs/id/' + this.modal_id)
                    .then((response) => {
                        this.department = response.data.department;
                        this.sector = response.data.sector;
                        this.service = response.data.service;
                        this.person = response.data.person;
                        this.budget = response.data.budget;
                        this.tax_rate = response.data.tax_rate * 100;
                        this.final = response.data.final;
                        this.comment = response.data.comment;
                        this.manual_actuals = response.data.manual_actuals;
                        this.tag = response.data.manual_actuals_tag;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        },
        // Deleted the DB entry and emits
        deleteClicked: async function () {
            const endpoint = '/api/costs/delete/' + this.modal_id;
            const response = await axios.post(endpoint, {})
                .catch(function (error) {
                    console.log(error);
                });
            await this.$emit('exit-delete', this.modal_id);
        },
        // Just emit
        cancelClicked: function () {
            this.$emit('exit-no-change', true);
        },
        // Form submit: do the validation and either create new or update existing
        checkForm: async function (e) {
            e.preventDefault();
            if (this.service && this.budget && (this.tax_rate || this.tax_rate === 0)) {
                var is_new;
                var id = this.modal_id;
                var response;
                var endpoint;
                if (id === -1) {
                    endpoint = '/api/costs/create';
                    is_new = true;
                } else {
                    endpoint = '/api/costs/update/' + this.modal_id;
                    is_new = false;
                }
                response = await axios.post(endpoint, {
                    department: this.department,
                    project_id: this.project_id,
                    sector: this.sector,
                    service: this.service,
                    person: this.person,
                    company: this.company,
                    budget: this.budget,
                    tax_rate: this.tax_rate / 100,
                    final: this.final,
                    comment: this.comment,
                    manual_actuals: (this.manual_actuals ? this.manual_actuals : 0),
                    manual_actuals_tag: this.tag
                })
                    .catch(function (error) {
                        console.log(error);
                    });

                const object = await response.data;
                id = await object.id;
                await this.$emit('exit-with-change', id, is_new, object);
            }
            this.errors = [];

            if (!this.service) {
                this.errors.push('Service required.');
            }
            if (!this.budget || isNaN(this.budget)) {
                this.errors.push('Budget required.');
            }
            if (this.tax_rate !== '0' || isNaN(this.tax_rate)) {
                this.errors.push('Tax Rate required.');
            }
        },
    },
};
</script>
<template>
    <div class="modal" style="display: block" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span v-if="modal_id == '-1'">Add New Cost Item</span>
                        <span v-else>Edit cost item ID: {{ modal_id }}</span></h5>
                </div>

                <div v-if="errors.length">
                    <b>Please correct the following error(s):</b>
                    <ul>
                        <li v-for="error in errors">{{ error }}</li>
                    </ul>
                </div>

                <form @submit="checkForm">
                    <div class="modal-body">

                        <label>Department:</label><br>
                        <input type="text" id="department" name="department" v-model="department"><br>
                        <label>Sector:</label><br>
                        <input type="text" id="sector" name="sector" v-model="sector"><br>
                        <label>Service:</label><br>
                        <input type="text" id="service" name="service" v-model="service" required><br>
                        <label>Person:</label><br>
                        <input type="text" id="person" name="person" v-model="person"><br>
                        <label>Company:</label><br>
                        <input type="text" id="company" name="company" v-model="company"><br>
                        <label>Budget (EUR):</label><br>
                        <input type="text" id="budget" name="budget" v-model="budget" required><br>
                        <label>Tax Rate (%):</label><br>
                        <input type="text" id="tax_rate" name="tax_rate" v-model="tax_rate" required><br>
                        <label>Comment:</label><br>
                        <input type="text" id="comment" name="comment" v-model="comment"><br>
                        <label>Manual actual cost entry:</label><br>
                        <input type="text" id="manual_actuals" name="manual_actuals" v-model="manual_actuals"><br>
                        <label>Manual cost tag:</label><br>
                        <input type="text" id="tag" name="tag" v-model="tag"><br>
                        <label>Set cost to be final:</label><br>
                        <input type="checkbox" id="final" name="final" v-model="final"
                               :checked="final"><br>
                        <br>
                    </div>
                    <div class="modal-footer">
                        <button @click="cancelClicked" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button v-if="modal_id > -1" @click="deleteClicked" type="button" class="btn btn-danger">
                            Delete
                        </button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
