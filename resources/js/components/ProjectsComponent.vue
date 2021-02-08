<template>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Project Overview</div>

                    <div class="card-body">

                        <div>
                            <button @click="openModal(-1)" type="button" class="btn btn-primary">Add New Project
                            </button>
                        </div>
                        <br>
                        <div v-if="update_msg" class="alert alert-success" role="alert">
                            {{ update_msg }}
                        </div>


                        <table class="table table-striped">

                            <tr>
                                <th v-for="header in headers">{{ header }}</th>
                                <th>Action</th>
                                <th>Open Project</th>
                            </tr>

                            <tr v-for="project in sortProject(projects)">
                                <td v-for="(data, key) in project" v-if="render.includes(key)">
                                    <span v-if="key == 'active'">
                                        {{ activePrint(data) }}
                                    </span>
                                    <span v-else>
                                        {{ data }}
                                    </span>
                                </td>
                                <td>
                                    <span>
                                        <button @click="openModal(project.id)" type="button" class="btn">Edit
                                    </button>
                                    </span>
                                </td>
                                <td>
                                    <a :href="'/project_editor/' + project.id" class="btn btn-primary">
                                        Open
                                    </a>

                                </td>
                            </tr>

                        </table>
                        <project-modal v-if="isModalVisible" @exit-no-change="exitNoChange"
                                       @exit-with-change="exitWithChange"
                                       @exit-delete="exitWithDelete"
                                       v-bind:modal_id="modal_id"></project-modal>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import EditProjectModal from "./EditProjectModal";

export default {
    components: {
        EditProjectModal,
    },
    data: function () {
        return {
            projects: [],
            headers: [],
            isModalVisible: false,
            update_msg: '',
            modal_id: -1,
            translate: {
                'id': 'ID',
                'name': 'Name',
                'client': 'Client',
                'active': 'Active',
            },
            render: [],
        }
    },
    mounted() {
        this.genRenderList();
        this.loadTransactions();
    },
    methods: {
        activePrint: function (active) {
            if (active === 0) {
                return 'No';
            }
            return 'Yes';
        },
        sortProject: function (arr) {
            // Set slice() to avoid to generate an infinite loop!
            return arr.slice().sort(
                (a, b) => a.name.localeCompare(b.name));
        },
        genRenderList() {
            this.render = Object.keys(this.translate);
        },
        openModal(id = -1) {
            this.modal_id = id
            this.isModalVisible = true;
        },
        exitNoChange: function () {
            this.isModalVisible = false;
            this.$router.push({name: 'ProjectList'})
        },
        exitWithChange: function (id, is_new, object) {
            this.isModalVisible = false;
            this.modal_id = -1;
            var ID_to_update;
            if (is_new) {
                this.update_msg = 'Created new project ID: ' + id;
                ID_to_update = this.projects.length;
            } else {
                this.update_msg = 'Updated project ID:' + id;
                ID_to_update = this.findId(id);
            }
            this.$set(this.projects, ID_to_update, object);
        },
        exitWithDelete: function (id) {
            this.isModalVisible = false;
            this.modal_id = -1;
            this.update_msg = 'Deleted project ID: ' + id;
            var ID_to_del = this.findId(id);
            this.$delete(this.projects, ID_to_del);
        },
        findId: function (id) {
            for (const [pos, project] of this.projects.entries()) {
                if (project.id == id) {
                    return pos;
                }
            }
            return null;
        },
        loadHeaders: function () {
            axios.get('/api/projects/headers')
                .then((response) => {
                    this.headers = response.data;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        loadTransactions: function () {
            axios.get('/api/projects/all')
                .then((response) => {
                    for (const item of response.data) {
                        this.projects.push(item);
                    }
                    this.generateHeaders();
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        generateHeaders() {
            if (Object.keys(this.projects).length) {
                this.headers = [];
                var ids = Object.keys(this.projects);
                var first_item = this.projects[ids[0]];
                for (var key in first_item) {
                    if (key in this.translate) {
                        this.headers.push(this.translate[key]);
                    } else {
                        this.headers.push(key);
                    }
                }
            }
        },
    }
}
</script>
