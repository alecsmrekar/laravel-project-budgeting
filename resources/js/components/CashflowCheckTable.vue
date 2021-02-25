<template>
    <div class="container" id="cashflow_page">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Cashflow Check</div>
                    <div class="card-body">

                        <div id="project_filter" class="filter">
                            <span>Filter projects:</span>
                            <select class="custom-select" @change="projectFilter($event)" id="filter_projects"
                                    v-model="filter_projects">
                                <option v-for="(label, opt) in filter_projects_options" v-bind:value="opt">{{ label }}
                                </option>
                            </select></div>
                        <div id="tags_filter" class="filter">
                            <span>Filter tags:</span>
                            <select class="custom-select" @change="tagFilter($event)" id="filter_tags"
                                    v-model="filter_tags">
                                <option v-for="(label, opt) in filter_tags_options" v-bind:value="opt">{{ label }}
                                </option>
                            </select></div>
                        <div id="status_filter" class="filter">
                            <span>Filter status:</span>
                            <select class="custom-select" @change="statusFilter($event)" id="filter_status"
                                    v-model="filter_status">
                                <option v-for="(label, opt) in filter_status_options" v-bind:value="opt">{{ label }}
                                </option>
                            </select></div>
                        <br><br>

                        <table class="table table-striped">

                            <tr>
                                <th v-for="h in headers">{{ h }}</th>
                            </tr>

                            <tr v-for="cf in cashflow" v-if="checkFilter(cf.project_id, cf.tag, cf.final)">
                                <td v-for="(item, key) in cf" v-if="key in headers">
                                    <span v-if="key != 'final'">{{ item }}</span>
                                    <span v-else>{{ final_status[item] }}</span>
                                </td>
                            </tr>
                            <tr style="font-weight: bold">
                                <td>Total Cash Change:</td>
                                <td v-for="i in Object.keys(headers).length-3"></td>
                                <td>{{ sum }}</td>
                                <td>{{ sum_tax }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    data: function () {
        return {
            final_status: {
                0: 'Open',
                1: 'Final',
            },
            filter_projects: -1,
            filter_projects_options: {
                '-1': ' - All - '
            },
            filter_tags: '',
            filter_tags_options: {
                '': ' - All - ',
            },
            filter_status: -1,
            filter_status_options: {
                '-1': ' - All - ',
                '0': 'Open',
                '1': 'Final'
            },
            headers: {
                'date': 'Date',
                'project_id': 'Project ID',
                'project': 'Project',
                'department': 'Department',
                'sector': 'Sector',
                'cost': 'Service',
                'final': 'Status',
                'tag': 'Tag',
                'actuals': 'Cashflow',
                'tax_part': 'Tax Part',
                'sum': 'Cumulative Cashflow',
                'sum_tax': 'Cumulative Taxes To Be Received',
            },
            cashflow: [],
            sum: 0,
            sum_tax: 0
        }
    },
    mounted() {
        this.loadCashflow();
    },
    methods: {
        checkFilter: function (project_id, tag, status) {
            if (this.filter_projects !== -1 && project_id !== this.filter_projects) {
                return false
            }
            if (this.filter_status !== -1 && status !== this.filter_status) {
                return false
            }
            if (this.filter_tags !== '' && tag !== this.filter_tags) {
                return false
            }
            return true
        },
        calcSum: function () {
            this.sum = 0
            this.sum_tax = 0
            for (const [key, row] of Object.entries(this.cashflow)) {
                if (this.checkFilter(row['project_id'], row['tag'], row['final'])) {
                    this.sum += row['actuals'];
                    this.sum_tax -= row['tax_part'];
                    row['sum'] = this.sum;
                    row['sum_tax'] = this.sum_tax;
                    this.$set(this.cashflow, key, row);
                }
            }
        },
        projectFilter: function (event) {
            this.filter_projects = parseInt(event.target.value);
            this.calcSum();
        },
        statusFilter: function (event) {
            this.filter_status = parseInt(event.target.value);
            this.calcSum();
        },
        tagFilter: function (event) {
            this.filter_tags = event.target.value;
            this.calcSum();
        },
        loadCashflow: function () {
            axios.get('/api/cashflow/all')
                .then((response) => {
                    for (const item of response.data) {
                        // sort the fields based on headers
                        let insert = {};
                        for (let key in this.headers) {
                            if (key in item) {
                                insert[key] = item[key];
                            }
                        }
                        if (item['project_id'] in this.filter_projects_options === false) {
                            this.filter_projects_options[item['project_id']] = item['project'];
                        }
                        if (item['tag'] in this.filter_tags_options === false) {
                            this.filter_tags_options[item['tag']] = item['tag'];
                        }
                        this.cashflow.push(insert);
                    }
                    this.calcSum();
                })
                .catch(function (error) {
                    console.log(error);
                });
        }
    }
}
</script>
