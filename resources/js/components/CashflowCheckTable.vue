<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Cashflow Check</div>
                    <div class="card-body">
                        <table class="table table-striped">

                            <tr>
                                <th v-for="h in headers">{{ h }}</th>
                            </tr>

                            <tr>
                                <td></td>
                            </tr>
                            <tr style="font-weight: bold">
                                <td>Total:</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>100</td>
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
            headers: {
                'date': 'Date',
                'project': 'Project',
                'desc': 'Description',
                'value': 'Value',
                'cum': 'Cumulative',
            },
        }
    },
    mounted() {
        this.loadCashflow();
    },
    methods: {
        loadCashflow: function () {
            axios.get('/api/transactions/all2')
                .then((response) => {
                    for (const item of response.data) {
                        this.transactions.push(item);
                    }
                    this.generateHeaders();
                })
                .catch(function (error) {
                    console.log(error);
                });
        }
    }
}
</script>
