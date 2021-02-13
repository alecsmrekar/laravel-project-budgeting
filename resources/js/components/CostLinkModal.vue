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
            link_id: -1,
            department: '',
            cost_id: -1,
            all_projects: [],
            all_departments: [],
            all_costs: [],
            filtered_costs: [],
            errors: [],
            tree: [],
        }
    },
    mounted() {
        this.loadProjectList();
        this.findExistingLink();
    },
    methods: {
        findExistingLink: async function () {
            var endpoint = '/api/links/all/' + this.modal_provider + '/' + this.modal_id;
            var data = await this.loadApiArrayAsync(endpoint);
            if (data.length > 0) {
                this.link_id = data[0]['id'];
                this.project = data[0]['project_id'];
                this.cost_id = data[0]['cost_id'];

                await this.loadTree(this.project);
                this.department = data[0]['department'];
                var dep_costs = await this.tree[this.department];
                var cnt = 0;
                for (let id in dep_costs) {
                    const item = {
                        'id': id,
                        'name': dep_costs[id],
                    };
                    this.filtered_costs.push(item);
                }
            }
        },
        cancelClicked: function () {
            this.$emit('exit-no-change', true);
        },
        deleteClicked: function () {
            const endpoint = '/api/links/delete/' + this.link_id;
            axios.post(endpoint, {})
                .then(function (response) {})
                .catch(function (error) {
                    console.log(error);
                });
            this.$emit('exit-with-delete', this.modal_provider, this.modal_id);
        },
        submitClicked: async function (e) {
            e.preventDefault();
            if (this.project && this.cost_id && this.modal_provider) {
                var endpoint;
                var is_new;
                if (this.link_id === -1) {
                    endpoint = '/api/links/create';
                    is_new = true;
                } else {
                    endpoint = '/api/links/update/' + this.modal_provider + '/' + this.link_id;
                    is_new = false;
                }
                var response = await axios.post(endpoint, {
                    cost_id: this.cost_id,
                    transaction_id: this.modal_id,
                    provider: this.modal_provider,
                })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
            await this.$emit('exit-with-change', this.modal_id, this.is_new)
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
        loadTree: async function (project_id) {
            for (var i = 0; i < this.all_departments.length; i++) {
                this.$delete(this.all_departments, i);
            }
            for (var i = 0; i < this.filtered_costs.length; i++) {
                this.$delete(this.filtered_costs, i);
            }
            var endpoint = '/api/projects/tree/' + this.project;
            var data;
            await axios.get(endpoint)
                .then((response) => {
                    this.all_departments = Object.keys(response.data);
                    this.all_costs = Object.values(response.data);
                    this.tree = response.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            for (var i = 0; i < this.all_departments.length; i++) {
                this.$set(this.all_departments, i, this.all_departments[i]);
            }
        },
        projectChange: async function (event) {
            if (event.target.value === this.project) {
                return;
            }
            this.cost_id = -1;
            this.department = '';
            this.loadTree(event.target.value);
        },
        departmentChange: function (event=false) {
            for (var i = 0; i < this.filtered_costs.length; i++) {
                this.$delete(this.filtered_costs, i);
            }
            var dep_costs = this.tree[this.department];
            var cnt = 0;
            for (let id in dep_costs) {
                const item = {
                    'id': id,
                    'name': dep_costs[id],
                };
                this.$set(this.filtered_costs, cnt, item);
                cnt++;
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
                        <select @change="departmentChange($event)" id="department" name="department"
                                v-model="department">
                            <option v-for="item in all_departments" v-bind:value="item">
                                {{ item }}
                            </option>
                        </select>
                        <br>
                        <label>Service:</label>
                        <select id="cost_id" name="cost_id" v-model="cost_id">
                            <option v-for="item in filtered_costs" v-bind:value="item.id">{{ item.name }}</option>
                        </select>

                        <br>
                    </div>


                    <div class="modal-footer">
                        <button @click="cancelClicked" type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button v-if="link_id > -1" @click="deleteClicked" type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            Delete
                        </button>
                        <button @click="submitClicked" type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
