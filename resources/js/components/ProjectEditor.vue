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
            aggregate_class: 'agg',
            modal_id: -1,
            filter_final: -1,
            filter_final_options: {
                '-1': 'All',
                0: 'Open',
                1: 'Final'
            },
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
                'comment': 'Comment',
                'actuals': 'Actual Cost',
                'actuals_net': 'Actual Cost (no tax)',
                'tax_part': 'Tax Amount',
                'diff': 'Diff vs Budget'
            },
            render: [],
            aggregates: {},
            aggregate_rows: [],
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
                    }
                }
            } else {
                for (var key in this.translate) {
                    this.headers.push(this.translate[key]);
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
            this.calcAggregates();
        },
        exitWithDelete: function (id) {
            this.isModalVisible = false;
            this.modal_id = -1;
            this.update_msg = 'Deleted cost ID: ' + id;
            var ID_to_del = this.findId(id);
            this.$delete(this.costs, ID_to_del);
            this.calcAggregates();
        },
        findId: function (id) {
            for (const [pos, cost] of this.costs.entries()) {
                if (cost.id === id) {
                    return pos;
                }
            }
            return null;
        },
        loadTransactions: function () {
            axios.get('/api/costs/all2/' + this.id)
                .then((response) => {
                    for (const item of response.data) {
                        this.costs.push(item);

                    }
                    this.generateHeaders();
                    this.calcAggregates();
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
        finalFilter: function (event) {
            this.filter_final = parseInt(event.target.value);
            this.calcAggregates();
        },
        calcAggregates: function () {
            let filter = this.filter_final;
            this.aggregates = {
                'budget': 0,
                'actuals': 0,
                'actuals_net': 0,
                'tax_part': 0,
                'diff': 0,
                'deps': {}
            };
            for (const [key, row] of Object.entries(this.sortCost(this.costs))) {
                if (row['final'] === filter || filter === -1) {
                    let dep = row['department'];
                    const sector = row['sector'];

                    if (!(dep in this.aggregates['deps'])) {
                        this.aggregates['deps'][dep] = {
                            'budget': 0,
                            'actuals': 0,
                            'actuals_net': 0,
                            'tax_part': 0,
                            'diff': 0,
                            'children': {
                                [sector]: {
                                    'budget': 0,
                                    'actuals': 0,
                                    'actuals_net': 0,
                                    'tax_part': 0,
                                    'diff': 0,
                                }
                            },
                        }
                    } else if (!(sector in this.aggregates['deps'][dep]['children'])) {
                        this.aggregates['deps'][dep]['children'][sector] = {
                            'budget': 0,
                            'actuals': 0,
                            'actuals_net': 0,
                            'tax_part': 0,
                            'diff': 0,
                        }
                    }
                    this.aggregates['budget'] += row['budget'];
                    this.aggregates['actuals'] += row['actuals'];
                    this.aggregates['actuals_net'] += row['actuals_net'];
                    this.aggregates['tax_part'] += row['tax_part'];
                    this.aggregates['diff'] += row['diff'];

                    this.aggregates['deps'][dep]['budget'] += row['budget'];
                    this.aggregates['deps'][dep]['actuals'] += row['actuals'];
                    this.aggregates['deps'][dep]['actuals_net'] += row['actuals_net'];
                    this.aggregates['deps'][dep]['tax_part'] += row['tax_part'];
                    this.aggregates['deps'][dep]['diff'] += row['diff'];

                    this.aggregates['deps'][dep]['children'][sector]['budget'] += row['budget'];
                    this.aggregates['deps'][dep]['children'][sector]['actuals'] += row['actuals'];
                    this.aggregates['deps'][dep]['children'][sector]['actuals_net'] += row['actuals_net'];
                    this.aggregates['deps'][dep]['children'][sector]['tax_part'] += row['tax_part'];
                    this.aggregates['deps'][dep]['children'][sector]['diff'] += row['diff'];
                }
            }
            let rows = [];
            for (const [key_1, row_1] of Object.entries(this.aggregates['deps'])) {
                for (const [key_2, row_2] of Object.entries(row_1['children'])) {
                    let row = {
                        'department': key_1,
                        'sector': key_2,
                        'budget': row_2['budget'],
                        'actuals': row_2['actuals'],
                        'actuals_net': row_2['actuals_net'],
                        'tax_part': row_2['tax_part'],
                        'diff': row_2['diff'],
                        'class': 3,
                    }
                    rows.push(row);
                }
                let row = {
                    'department': key_1,
                    'sector': 'All',
                    'budget': row_1['budget'],
                    'actuals': row_1['actuals'],
                    'actuals_net': row_1['actuals_net'],
                    'tax_part': row_1['tax_part'],
                    'diff': row_1['diff'],
                    'class': 2,
                }
                rows.push(row);
            }
            let row = {
                'department': 'Entire Project',
                'sector': '',
                'budget': this.aggregates['budget'],
                'actuals': this.aggregates['actuals'],
                'actuals_net': this.aggregates['actuals_net'],
                'tax_part': this.aggregates['tax_part'],
                'diff': this.aggregates['diff'],
                'class': 1,
            }
            rows.push(row);
            this.aggregate_rows = rows;
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
    <div class="container" id="project_editor_page">
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


                        <span>Filter on final status:</span>

                        <select class="custom-select" @change="finalFilter($event)" id="filter_final"
                                v-model="filter_final">
                            <option v-for="(label, opt) in filter_final_options" v-bind:value="opt">{{ label }}</option>
                        </select><br><br><h4>Cost Overview</h4>

                        <table class="table table-striped">
                            <tr>
                                <th v-for="h in headers" v-if="Object.values(translate).indexOf(h) > -1">{{ h }}</th>
                                <th>Edit</th>
                            </tr>
                            <tr v-for="cost in sortCost(costs)" v-if="filter_final === -1 || cost.final===filter_final">
                                <td v-for="(item, key) in cost" v-if="render.includes(key)">
                                    <span v-if="key == 'tax_rate'">
                                    {{ item * 100 }}
                                    </span>
                                    <span v-else-if="key == 'final'">
                                    {{ filter_final_options[item] }}
                                    </span>
                                    <span v-else-if="key == 'actuals' || key == 'actuals_net' || key == 'tax_part'" >
                                    {{ item *-1 }}
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

                        <br>
                        <h4>Aggregates</h4>

                        <table class="table table-striped" id="aggregates">
                            <tr>
                                <th>Department</th>
                                <th>Sector</th>
                                <th>Budget</th>
                                <th>Actual Cost</th>
                                <th>Actual Cost Net</th>
                                <th>Tax Part</th>
                                <th>Diff vs Budget</th>
                            </tr>
                            <tr v-for="row in aggregate_rows">
                                <td v-for="(cell, key) in row" v-if="key != 'class'" v-bind:class="[row.class < 3 ? 'agg': '']">
                                    <span v-if="key == 'actuals' || key == 'actuals_net' || key == 'tax_part'" >
                                    {{ cell *-1 }}
                                    </span>
                                    <span v-else>
                                        {{cell}}
                                    </span>
                                </td>

                            </tr>
                        </table>


                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
