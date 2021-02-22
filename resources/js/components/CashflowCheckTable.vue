<template>
    <div class="container" id="cashflow_page">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Cashflow Check</div>
                    <div class="card-body">

                        <div id="project_filter">
                        <span>Filter projects:</span>
                        <select class="custom-select" @change="projectFilter($event)" id="filter_projects" v-model="filter_projects">
                            <option v-for="(label, opt) in filter_projects_options" v-bind:value="opt">{{
                                    label
                                }}
                            </option>
                        </select></div><br><br>

                        <table class="table table-striped">

                            <tr>
                                <th v-for="h in headers">{{ h }}</th>
                            </tr>

                            <tr v-for="cf in cashflow" v-if="filter_projects === -1 || cf.project_id===filter_projects">
                                <td v-for="(item, key) in cf" v-if="key in headers">
                                    <span v-if="key != 'final'">{{ item }}</span>
                                    <span v-else>{{ final_status[item] }}</span>
                                </td>
                            </tr>
                            <tr style="font-weight: bold">
                                <td>Total Cash Change:</td>
                                <td v-for="i in Object.keys(headers).length-2"></td>
                                <td>{{ sum }}</td>
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
                '-1': 'All'
            },
            headers: {
                'date': 'Date',
                'project_id': 'Project ID',
                'project': 'Project',
                'department': 'Department',
                'sector': 'Sector',
                'cost': 'Service',
                'final': 'Status',
                'amount': 'Amount',
                'sum': 'Cumulative',
            },
            cashflow: [],
            sum: 0,
        }
    },
    mounted() {
        this.loadCashflow();
    },
    methods: {
        calcSum: function () {
            this.sum = 0
            for (const [key, row] of Object.entries(this.cashflow)) {
                if (row['project_id'] === this.filter_projects || this.filter_projects === -1) {
                    this.sum += row['amount'];
                    row['sum'] = this.sum;
                    this.$set(this.cashflow, key, row);
                }
            }
        },
        projectFilter: function (event) {
            this.filter_projects = parseInt(event.target.value);
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
