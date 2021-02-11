<script>
export default {
    name: 'CostLinkModal',
    props: {
        modal_id: {
            type: Number,
            default: -1,
        },
        modal_provider: {
            type: String,
            default: '',
        },
    },
    data: function () {
        return {
            project: -1,
            department: '',
            cost_id: -1,
            all_projects: [],
            all_departments: [],
            all_costs: [],
            errors: [],
            tree:[],
        }
    },
    mounted() {
        this.loadProjectList();
    },
    methods: {
        cancelClicked: function () {
            this.$emit('exit-no-change', true)
        },
        deleteClicked: function () {
            this.$emit('exit-no-change', true)
        },
        submitClicked: function () {
            this.$emit('exit-with-change', true)
        },
        loadProjectList: function () {
            this.all_projects = this.loadApiArraySync('/api/projects/all');
        },
        loadApiArraySync: function (endpoint) {
            var data = [];
            axios.get(endpoint)
                .then((response) => {
                    for (const item of response.data) {
                        data.push(item);
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            return data;
        },
        loadApiArrayAsync: async function (endpoint) {
            var data = [];
            await axios.get(endpoint)
                .then((response) => {
                    for (const item of response.data) {
                        data.push(item);
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });
            return data;
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
        projectChange: async function (event) {
            if (event.target.value === this.project) {
                return;
            }
            for (var i = 0; i< this.all_departments.length; i++) {
                this.$delete(this.all_departments, i);
            }
            for (var i = 0; i< this.all_costs.length; i++) {
                this.$delete(this.all_costs, i);
            }
            this.project = event.target.value;
            var endpoint = '/api/projects/tree/' + this.project;
            var data;
            await axios.get(endpoint)
                .then((response) => {
                    this.all_departments = Object.keys(response.data);
                    this.all_costs = Object.values(response.data);
                    data = response.data;
                    this.tree=response.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            for (var i = 0; i< this.all_departments.length; i++) {
                this.$set(this.all_departments,i, this.all_departments[i]);
            }
        },
        departmentChange: function (event) {
            for (var i = 0; i< this.all_costs.length; i++) {
                this.$delete(this.all_costs, i);
            }
            var dep_costs = this.tree[this.department];
            var cnt = 0;
            for (let id in dep_costs) {
                console.log(id + ' is ' + dep_costs[id]);
                this.$set(this.all_costs, cnt, dep_costs[id]);
                cnt++;
            }
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
                id = await object.id;
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
                    <h5 class="modal-title">
                        <span>Link transaction</span></h5>
                </div>
                <form>
                    <div class="modal-body">
                        <label>Project:</label>
                        <select @change="projectChange($event)" id="project" name="project" v-model="project">
                            <option v-for="item in all_projects" v-bind:value="item.id">{{ item.name }}</option>
                        </select>
                        <br>
                        <label>Department:</label>
                        <select @change="departmentChange($event)" id="department" name="department" v-model="department">
                            <option v-for="item in all_departments" v-bind:value="item">
                                {{ item }}
                            </option>
                        </select>
                        <br>
                        <label>Service:</label>
                        <select id="cost_id" name="cost_id" v-model="cost_id">
                            <option v-for="item in all_costs" v-bind:value="item">{{ item }}</option>
                        </select>

                        <br>
                    </div>


                    <div class="modal-footer">
                        <button @click="cancelClicked" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button v-if="modal_id > -1" @click="deleteClicked" type="button" class="btn btn-danger">
                            Delete
                        </button>
                        <button @click="submitClicked" type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
