<script>
import NewCostItem from "./CostItemModal";

export default {
    name: 'ProjectEditor',
    components: {
        NewCostItem,
    },
    mounted() {
        this.genRenderList();
        this.recordID();
        this.fetchProjectInfo();
        this.loadTransactions();
    },
    data: function () {
        return {
            id: -1,
            modal_id: -1,
            isModalVisible: false,
            cname: '',
            update_msg: '',
            cclient: '',
            active: 'Yes',
            costs: [],
            headers: [],
            translate: {
                'id': 'ID',
                'department': 'Department',
                'sector': 'Sector',
                'service': 'Service',
                'person': 'Person',
                'company': 'Company',
                'budget': 'Budget (EUR)',
                'tax_rate': 'Tax Rate %',
                'final': 'Is Final?',
                'comment': 'Comment'
            },
            render: [],
        }
    },
    methods: {
        sortCost: function (arr) {
            // Set slice() to avoid to generate an infinite loop!
            return arr.slice().sort(
                (a, b) => a.department.localeCompare(b.department));

            // (a, b) => a.department.localeCompare(b.department) || a.sector.localeCompare(b.sector));
        },
        genRenderList() {
            this.render = Object.keys(this.translate);
        },
        generateHeaders() {
            if (Object.keys(this.costs).length) {
                this.headers = [];
                var ids = Object.keys(this.costs);
                var first_item = this.costs[ids[0]];
                for (var key in first_item) {
                    if (key in this.translate) {
                        this.headers.push(this.translate[key]);
                    } else {
                        this.headers.push(key);
                    }
                }
            }
        },
        exitWithChange: function (id, is_new, object) {
            this.isModalVisible = false;
            this.modal_id = -1;
            var ID_to_update;
            if (is_new) {
                this.update_msg = 'Created new cost ID: ' + id;
                ID_to_update = this.costs.length;
            } else {
                this.update_msg = 'Updated cost ID:' + id;
                ID_to_update = this.findId(id);
            }
            this.$set(this.costs, ID_to_update, object);
        },
        exitWithDelete: function (id) {
            this.isModalVisible = false;
            this.modal_id = -1;
            this.update_msg = 'Deleted cost ID: ' + id;
            var ID_to_del = this.findId(id);
            this.$delete(this.costs, ID_to_del);
        },
        findId: function (id) {
            for (const [pos, cost] of this.costs.entries()) {
                if (cost.id === id) {
                    return pos;
                }
            }
            return null;
        },
        fetchOne(id) {
            var data = [];
            axios.get('/api/costs/id/' + id)
                .then((response) => {
                    data = response.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
            return data;
        },
        loadTransactions: function () {
            axios.get('/api/costs/all/' + this.id)
                .then((response) => {
                    for (const item of response.data) {
                        this.costs.push(item);
                    }
                    this.generateHeaders();
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        recordID() {
            this.id = parseInt(this.$route.params.id);
        },
        exitNoChange: function () {
            this.isModalVisible = false;
            this.modal_id = -1;
        },
        openModal(id = -1) {
            this.modal_id = id;
            this.isModalVisible = true;
        },
        fetchProjectInfo() {
            if (this.id !== '-1') {
                axios.get('/api/projects/id/' + this.id)
                    .then((response) => {
                        this.cname = response.data.name;
                        this.cclient = response.data.client;
                        this.active = 'Yes';
                        if (response.data.active === 0) {
                            this.active = 'No';
                        }
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        },
    },
};
</script>
<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Project Editor: <span style="font-style: italic">{{ cname }}</span></div>
                    <div class="card-body">

                        <div>Project Name: {{ cname }}</div>
                        <div>Project Client: {{ cclient }}</div>
                        <div>Project Active: {{ active }}</div>
                        <br>
                        <div v-if="update_msg" class="alert alert-success" role="alert">
                            {{ update_msg }}
                        </div>
                        <div>
                            <button @click="openModal(-1)" type="button" class="btn btn-primary">Add Cost Item</button>
                        </div>
                        <br>

                        <table class="table table-striped">
                            <tr>
                                <th v-for="h in headers" v-if="Object.values(translate).indexOf(h) > -1">{{ h }}</th>
                                <th>Edit</th>
                            </tr>
                            <tr v-for="cost in sortCost(costs)">
                                <td v-for="(item, key) in cost" v-if="render.includes(key)">
                                    <span v-if="key == 'tax_rate'">
                                    {{ item * 100 }}
                                    </span>
                                    <span v-else>
                                        {{ item }}
                                    </span>
                                </td>
                                <td>
                                    <button @click="openModal(cost.id)" type="button" class="btn btn-primary">Edit
                                    </button>
                                </td>
                            </tr>
                        </table>

                        <new-cost-item v-if="isModalVisible" @exit-no-change="exitNoChange"
                                       @exit-with-change="exitWithChange" @exit-delete="exitWithDelete"
                                       v-bind:modal_id="modal_id"
                                       v-bind:project_id="id"
                                       v-bind:all_costs="costs"></new-cost-item>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
