<template>
  <el-card>
    <template v-slot:header>
      <content-header/>
    </template>

    <search-form :fields="search"/>

    <el-table :data="perms">
      <el-table-column prop="id" label="ID" width="60"/>
      <el-table-column prop="name" label="名称" width="150"/>
      <el-table-column prop="slug" label="标识" width="150"/>
      <el-table-column label="路由" min-width="400">
        <template v-slot="{ row }">
          <route-show :data="row"/>
        </template>
      </el-table-column>
      <el-table-column prop="created_at" label="添加时间" width="180"/>
      <el-table-column prop="updated_at" label="修改时间" width="180"/>
      <el-table-column label="操作" width="150">
        <template v-slot="{ row, $index }">
          <el-button-group>
            <el-button size="small" class="link">
              <router-link :to="`/admin-permissions/${row.id}/edit`">编辑</router-link>
            </el-button>
            <pop-confirm
              type="danger"
              size="small"
              :confirm="onDestroy($index)"
            >
              删除
            </pop-confirm>
          </el-button-group>
        </template>
      </el-table-column>
    </el-table>
    <div class="card-footer">
      <pagination :page="page"/>
    </div>
  </el-card>
</template>

<script>
import { destroyAdminPerm, getAdminPerms } from '@/api/admin-perms'
import RouteShow from './components/RouteShow'
import PopConfirm from '@c/PopConfirm'
import Pagination from '@c/Pagination'
import SearchForm from '@c/SearchForm'
import { getMessage } from '@/libs/utils'

export default {
  name: 'Index',
  components: {
    SearchForm,
    Pagination,
    PopConfirm,
    RouteShow,
  },
  data() {
    return {
      perms: [],
      page: null,
      searchForm: {},
      search: [
        {
          field: 'id',
          label: 'ID',
        },
        {
          field: 'name',
          label: '名称',
        },
        {
          field: 'slug',
          label: '标识',
        },
        {
          field: 'http_path',
          label: '请求路径',
        },
      ],
    }
  },
  methods: {
    onDestroy(index) {
      return async () => {
        await destroyAdminPerm(this.perms[index].id)
        this.perms.splice(index, 1)
        this.$message.success(getMessage('destroyed'))
      }
    },
  },
  watch: {
    $route: {
      async handler(newVal) {
        const { data: { data, meta } } = await getAdminPerms(newVal.query)
        this.perms = data
        this.page = meta
      },
      immediate: true,
    },
  },
}
</script>
