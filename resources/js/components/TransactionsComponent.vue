<template>

    <div class="container" id="transactions_page">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Transaction List</div>

                    <div class="card-body">
                        <div v-if="update_msg" class="alert alert-success" role="alert">
                            {{ update_msg }}
                        </div>

                        <table class="table table-striped">

                            <tr>
                                <th v-for="h in headers" v-if="Object.values(translate).indexOf(h) > -1">{{ h }}</th>
                                <th>Link</th>
                            </tr>

                            <tr v-for="transaction in sortTran(transactions)">
                                <td v-for="(data, key) in transaction"  v-if="render.includes(key)">
                                    {{ data }}

                                </td>
                                <td>
                                    <button v-if="transaction.status == 'Linked'" @click="openModal(transaction.number, transaction.provider)" type="button"
                                            class="btn btn-secondary">Edit Link</button>
                                    <button v-else @click="openModal(transaction.number, transaction.provider)" type="button"
                                            class="btn btn-success">Add Link</button>
                                </td>
                            </tr>


                        </table>

                        <cost-link-modal v-if="isModalVisible" @exit-no-change="exitNoChange"
                                         @exit-with-change="exitWithChange"
                                         @exit-with-delete="exitWithDelete"
                                         v-bind:modal_id="modal_id"
                                         v-bind:modal_provider="modal_provider"
                        ></cost-link-modal>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import CostLinkModal from "./CostLinkModal";

export default {
    components: {CostLinkModal},
    data: function () {
        return {
            transactions: [],
            isModalVisible: false,
            headers: [],
            update_msg: '',
            translate: {
                'id': 'ID',
                'number': 'Number',
                'time': 'Time',
                'type': 'Type',
                'account': 'Account',
                'amount': 'Amount',
                'counterparty': 'Counterparty',
                'currency': 'Currency',
                'provider': 'Provider',
            },
            render: [],
            modal_id: -1,
            modal_provider: '',
        }
    },
    mounted() {
        this.genRenderList();
        this.loadTransactions();
    },
    methods: {
        openModal(id, provider) {
            this.modal_id = id;
            this.modal_provider = provider;
            this.isModalVisible = true;
        },
        exitNoChange: function () {
            this.isModalVisible = false;
            this.modal_id = -1;
            this.modal_provider = '';
        },
        findId: function (id, provider) {
            for (const [pos, data] of this.transactions.entries()) {
                if (data.number === id && data.provider === provider) {
                    return pos;
                }
            }
            return null;
        },
        exitWithChange: function (is_new) {
            this.isModalVisible = false;
            if (is_new) {
                this.update_msg = 'Created link on ' + this.modal_provider + ' transaction #' + this.modal_id;
            }else {
                this.update_msg = 'Updated link on ' + this.modal_provider + ' transaction #' + this.modal_id;
            }
            var ID_to_update = this.findId(this.modal_id, this.modal_provider);
            this.modal_id = -1;
            this.modal_provider = '';
            this.transactions[ID_to_update].status = 'Linked';
            this.$set(this.transactions, ID_to_update, this.transactions[ID_to_update]);
        },
        exitWithDelete: function (provider, id) {
            this.isModalVisible = false;
            this.update_msg = 'Deleted link on ' + provider + ' transaction #' + id;
            var ID_to_update = this.findId(id, provider);
            this.transactions[ID_to_update].status = 'Not Linked';
            this.$set(this.transactions, ID_to_update, this.transactions[ID_to_update]);
            this.modal_id = -1;
            this.modal_provider = '';
        },
        sortTran: function (arr) {
            // Set slice() to avoid to generate an infinite loop!
            return arr.slice().sort(
                (a, b) => b.number - a.number)
        },
        generateHeaders() {
            if (Object.keys(this.transactions).length) {
                this.headers = [];
                var ids = Object.keys(this.transactions);
                var first_item = this.transactions[ids[0]];
                for (var key in first_item) {
                    if (key in this.translate) {
                        this.headers.push(this.translate[key]);
                    }
                }
            }
        },
        genRenderList() {
            this.render = Object.keys(this.translate);
        },
        loadTransactions: function () {
            axios.get('/api/transactions/all')
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
