<script setup>
import { ref, onMounted, reactive, watch } from 'vue';
import { Form, Field } from 'vee-validate';
import * as yup from 'yup';
import { useToastr } from '../../toaster.js';
//import moment from 'moment';

//import { formatDate } from '../../helper.js';
import UserListItem from './UserListItem.vue';

import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';

// const users = [{
//     id: 1,
//     name: 'John Due',
//     email: 'johndue@test.com'
// }, {
//     id: 2,
//     name: 'John Smith',
//     email: 'johnSmith@test.com'
// }];

const users = ref({ 'data': [] });
const editing = ref(false);
const formValues = ref();
const form = ref(null); //to get the form
const toaster = useToastr();


const getUsers = (page = 1) => {
    axios.get(`/api/users?page=${page}`, {
        params: {
            query: searchQuery.value
        }
    })
        .then((response) => {
            users.value = response.data;
            selectedUsers.value = []; // onPagination pre selected checkbox will be get unchecked
            selectAll.value = false; // onPagination pre selected checkbox will be get unchecked
        });
}


// // in vue3 to make this object as reactive we need to warp insted of reactive function

// const formVal = reactive({
//     name: '',
//     email: '',
//     password: '',
// });

// validation for create user
const createUserSchema = yup.object({
    name: yup.string().required(),
    email: yup.string().email().required(),
    password: yup.string().required().min(8),
});

// validation for edit user
const editUserSchema = yup.object({
    name: yup.string().required(),
    email: yup.string().email().required(),
    password: yup.string().notRequired().test('password', 'Passwords must be be minimum of 8 characters', function (value) {
        if (!!value) {
            const schema = yup.string().min(8);
            return schema.isValidSync(value);
        }
        return true;
    }),
});

const createUserSubmit = (values, { resetForm, setErrors }) => {
    axios.post('/api/users', values).then((response) => {
        users.value.data.unshift(response.data);
        $('#userFormModal').modal('hide');
        resetForm();
        toaster.success('User created successfully!')
    }).catch((error) => {
        if (error.response.data.errors) {
            setErrors(error.response.data.errors);
        }
    });
}

const editUserSubmit = (values, { resetForm, setErrors }) => {
    //console.log(values);
    axios.put('/api/users/' + formValues.value.id, values).then((response) => {
        const index = users.value.findIndex(user => user.id === response.data.id);
        users.value[index] = response.data;
        $('#userFormModal').modal('hide');
        resetForm();
        toaster.success('User updated successfully!');
    }).catch((error) => {
        setErrors(error.response.data.errors);
        console.log(error);
    });
};

const handleSubmit = (values, actions) => {
    if (editing.value)
        editUserSubmit(values, actions);
    else
        createUserSubmit(values, actions);
};

// const createUser = () => {
//     axios.post('/api/users', formVal).then((response) => {
//         users.value.unshift(response.data);
//         formVal.name = '';
//         formVal.email = '';
//         formVal.password = '';
//     });
//     $('#userFormModal').modal('hide');
// }

const editUser = (user) => {
    form.value.resetForm(); // reset the form
    editing.value = true;
    formValues.value = {
        id: user.id,
        name: user.name,
        email: user.email,
        password: '',
    };
    $('#userFormModal').modal('show');
};

const addUser = () => {
    editing.value = false;
    $('#userFormModal').modal('show');
}

// const userDeleted = (userId) => {
//     users.value = users.value.filter(user => user.id !== userId);
// }

const searchQuery = ref(null);
// const search = () => {
//     axios.get('/api/users/search', {
//         params: {
//             query: searchQuery.value,
//         }
//     }).then(response => {
//         users.value = response.data;
//     }).catch(error => {
//         console.log(error)
//     });
// }

const selectedUsers = ref([]);
const toggleSelection = (user) => {
    const index = selectedUsers.value.indexOf(user.id);
    if (index === -1) {
        selectedUsers.value.push(user.id);
    } else {
        selectedUsers.value.splice(index, 1)
    }
    console.log(selectedUsers.value);
}

watch(searchQuery, debounce(() => {
    //search();
    getUsers();
}, 300))


const userIdBeignDeleted = ref(null);

const confirmUserDeletion = (id) => {
    userIdBeignDeleted.value = id;
    $('#userDeleteModal').modal('show');
}

const deleteUser = () => {
    axios.delete(`/api/users/${userIdBeignDeleted.value}`).then(() => {
        //users.value = users.value.filter(user => user.id !== userIdBeignDeleted.value);
        $('#userDeleteModal').modal('hide');
        toaster.success('User delete successfully');
        users.value.data = users.value.data.filter(user => user.id !== userIdBeignDeleted.value);
        //emit("userDeleted", userIdBeignDeleted.value);
    });
}

const bulkDelete = () => {
    axios.delete('/api/users', {
        data: {
            ids: selectedUsers.value,
        }
    }).then(response => {
        //alert('deleted');
        users.value.data = users.value.data.filter(user => !selectedUsers.value.includes(user.id));
        selectedUsers.value = [];
        selectAll.value = false;
        toaster.success(response.data.message);
    });
}

const selectAll = ref(false);
const selectAllUsers = () => {
    if (selectAll.value) {
        selectedUsers.value = users.value.data.map(user => user.id);
    } else {
        selectedUsers.value = [];
    }
}

onMounted(() => {
    getUsers();
});

</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Users</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <div class="d-flex">
                    <button @click="addUser" type="button" class="mb-2 btn btn-primary"><i
                            class="fa fa-plus-circle mr-1"></i>
                        Add
                        New User </button>
                    <div v-if="selectedUsers.length > 0">
                        <button @click="bulkDelete" type="button" class="ml-2 btn btn-danger"><i
                                class="fa fa-trash mr-1"></i>
                            Delete Selected </button>
                        <span class="ml-2">Selected {{ selectedUsers.length }} users</span>
                    </div>
                </div>
                <div>
                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search..." />
                    <!-- <button @click.prevent="search">Submit</button> -->
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 10px">
                                    <input v-model="selectAll" type="checkbox" @change="selectAllUsers">
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered Date</th>
                                <th>Role</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody v-if="users.data.length > 0">

                            <UserListItem v-for="(user, index) in users.data" :key="user.id" :user=user :index=index
                                @edit-user="editUser" @toggle-selection="toggleSelection" :select-all="selectAll"
                                @confirm-user-deletion="confirmUserDeletion" />
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="text-center"> No result found...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <Bootstrap4Pagination :data="users" @pagination-change-page="getUsers" />
        </div>
    </div>

    <div class="modal fade" id="userFormModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span v-if="editing">Edit User</span>
                        <span v-else>Add New User</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- to Get the Error we have to use v-slot -->
                <!-- for edit values prepopulate :initial-values="formValues" -->
                <Form ref="form" @submit="handleSubmit" :validation-schema="editing ? editUserSchema : createUserSchema"
                    v-slot="{ errors }" :initial-values="formValues">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <Field name="name" type="text" class="form-control" :class="{ 'is-invalid': errors.name }"
                                id="name" aria-describedby="nameHelp" placeholder="Enter full name" />
                            <span class="invalid-feedback"> {{ errors.name }} </span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <Field name="email" type="email" class="form-control" :class="{ 'is-invalid': errors.email }"
                                id="email" aria-describedby="emailHelp" placeholder="Enter full name" />
                            <span class="invalid-feedback"> {{ errors.email }} </span>
                        </div>

                        <div class="form-group">
                            <label for="email">Password</label>
                            <Field name="password" type="password" class="form-control"
                                :class="{ 'is-invalid': errors.password }" id="password" aria-describedby="passwordHelp"
                                placeholder="Enter password" />
                            <span class="invalid-feedback">{{ errors.password }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </Form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="userDeleteModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>Delete User</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Are you sure, you want to delete this user?</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button @click.prevent="deleteUser" type="button" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</template>