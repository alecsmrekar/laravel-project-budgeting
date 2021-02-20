<script>
export default {
    name: 'EditProjectModal',
    mounted() {
        this.loadProject();
    },
    props: {
        modal_id: {
            type: Number,
            default: -1,
        },
    },
    data: function () {
        return {
            cname: '',
            cclient: '',
            active: 1,
            testing: 'Testing',
            projects: [],
            errors: [],
        }
    },
    methods: {
        cancelClicked: function () {
            this.$emit('exit-no-change', true)
        },
        loadProject: function () {
            if (this.modal_id !== -1) {
                axios.get('/api/projects/id/' + this.modal_id)
                    .then((response) => {
                        this.cname = response.data.name;
                        this.cclient = response.data.client;
                        this.active = response.data.active;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        },
        deleteClicked: async function () {
            const endpoint = '/api/projects/delete/' + this.modal_id;
            const response = await axios.post(endpoint, {})
                .catch(function (error) {
                    console.log(error);
                });
            await this.$emit('exit-delete', this.modal_id);
        },
        submitEdit: function () {
            var endpoint = '/api/projects/update/' + this.modal_id;
            let currentObj = this;
            axios.post(endpoint, {
                name: this.cname,
                client: this.cclient,
                active: this.active
            })
                .then(function (response) {
                    currentObj.output = response.data;
                })
                .catch(function (error) {
                    currentObj.output = error;
                });
        },
        submitNew: function () {
            var endpoint = '/api/projects/create';
            let currentObj = this;
            axios.post(endpoint, {
                name: this.cname,
                client: this.cclient,
                active: this.active
            })
                .then(function (response) {
                    currentObj.output = response.data;
                })
                .catch(function (error) {
                    currentObj.output = error;
                });
        },
        checkForm: async function (e) {
            e.preventDefault();
            if (this.cname && this.cclient) {
                var is_new;
                var id = this.modal_id;
                var response;
                var endpoint;
                if (this.modal_id === -1) {
                    endpoint = '/api/projects/create';
                    is_new = true;
                } else {
                    endpoint = '/api/projects/update/' + this.modal_id;
                    is_new = false;
                }
                response = await axios.post(endpoint, {
                    name: this.cname,
                    client: this.cclient,
                    active: this.active
                })
                    .catch(function (error) {
                        console.log(error);
                    });
                const object = await response.data;
                id = await object['id'];
                await console.log(id, is_new, object)
                await this.$emit('exit-with-change', id, is_new, object);
            }
            this.errors = [];
            if (!this.cname) {
                this.errors.push('Name required.');
            }
            if (!this.cclient) {
                this.errors.push('Client required.');
            }
        }
    },
};
</script>
<template>
    <div class="modal" style="display: block" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span v-if="modal_id == '-1'">Add New Project</span>
                        <span v-else>Edit Project ID {{ modal_id }}</span></h5>
                </div>

                <p v-if="errors.length">
                    <b>Please correct the following error(s):</b>
                <ul>
                    <li v-for="error in errors">{{ error }}</li>
                </ul>
                </p>

                <form @submit="checkForm">
                    <div class="modal-body">

                        <label>Project name:</label><br>
                        <input type="text" id="name" name="name" v-model="cname" required><br>
                        <label>Client name:</label><br>
                        <input type="text" id="client" name="client" v-model="cclient" required><br>
                        <label>Set project to be active:</label><br>
                        <input type="checkbox" id="active" name="active" v-model="active"
                               :checked="active"><br>
                        <br>
                    </div>
                    <div class="modal-footer">
                        <button @click="cancelClicked" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button v-if="modal_id > -1" @click="deleteClicked" type="button" class="btn btn-danger">
                            Delete
                        </button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
