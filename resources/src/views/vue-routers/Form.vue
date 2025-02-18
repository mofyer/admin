<template>
  <el-card class="create">
    <template v-slot:header>
      <content-header/>
    </template>
    <el-row type="flex" justify="center">
      <lz-form
        ref="form"
        :get-data="getData"
        :on-submit="onSubmit"
        :errors.sync="errors"
        :form.sync="form"
      >
        <el-form-item label="父级路由" prop="parent_id">
          <el-select
            v-model="form.parent_id"
            filterable
            clearable
            placeholder="一级"
          >
            <el-option
              v-for="i of vueRouterOptions"
              :key="i.id"
              :label="i.title"
              :value="i.id"
            >
              <span>{{ i.text }}</span>
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item
          label="标题"
          required
          prop="title"
        >
          <el-input v-model="form.title"/>
        </el-form-item>
        <el-form-item label="地址" prop="path">
          <el-input v-model="form.path">
            <template slot="prepend">/admin/</template>
          </el-input>
        </el-form-item>
        <el-form-item label="图标" prop="icon">
          <el-input placeholder="请输入内容" v-model="form.icon" class="icon">
            <el-select v-model="form.icon" slot="prepend" placeholder="图标">
              <el-option
                v-for="i of icons"
                :key="i"
                label="图标"
                :value="i"
              >
                <svg-icon class="mr-2" :icon-class="i"/>
                <span class="fr">{{ i }}</span>
              </el-option>
            </el-select>
            <svg-icon slot="append" :icon-class="form.icon || 'cog-fill'"/>
          </el-input>
        </el-form-item>
        <el-form-item label="排序" prop="order">
          <el-input-number v-model="form.order" :min="-9999" :max="9999"/>
        </el-form-item>
        <el-form-item label="显示在菜单" prop="menu">
          <el-switch
            v-model="form.menu"
            active-text="显示"
            inactive-text="隐藏"
          />
        </el-form-item>
        <el-form-item label="缓存" prop="cache">
          <el-switch
            v-model="form.cache"
            active-text="缓存"
            inactive-text="不缓存"
          />
        </el-form-item>
        <el-form-item label="角色" prop="roles">
          <el-select
            v-model="form.roles"
            multiple
            placeholder="选择角色"
            filterable
            clearable
          >
            <el-option
              v-for="i of roles"
              :key="i.id"
              :label="i.name"
              :value="i.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="权限" prop="permission">
          <el-select
            v-model="form.permission"
            placeholder="选择权限"
            filterable
            clearable
          >
            <el-option
              v-for="i of permissions"
              :key="i.id"
              :label="i.name"
              :value="i.slug"
            />
          </el-select>
        </el-form-item>
      </lz-form>
    </el-row>
  </el-card>
</template>
<script>
import { nestedToSelectOptions, getMessage } from '@/libs/utils'
import { editVueRouter, getVueRouters, storeVueRouter, updateVueRouter } from '@/api/vue-routers'
import { isInt } from '@/libs/validates'
import LzForm from '@c/LzForm'
import { getAdminRoles } from '@/api/admin-roles'
import { getAdminPerms } from '@/api/admin-perms'
import FormHelper from '@c/LzForm/FormHelper'
import icons from '@/icons'

export default {
  name: 'Form',
  components: {
    LzForm,
  },
  mixins: [
    FormHelper,
  ],
  data() {
    return {
      form: {
        parent_id: 0,
        title: '',
        path: '',
        icon: '',
        order: 0,
        cache: false,
        menu: false,
        roles: [],
        permission: '',
      },
      errors: {},
      vueRouters: [],
      roles: [],
      permissions: [],
    }
  },
  computed: {
    vueRouterOptions() {
      return nestedToSelectOptions(this.vueRouters)
    },
    icons() {
      return icons
    },
  },
  methods: {
    queryParentId() {
      const id = Number.parseInt(this.$route.query.parent_id)
      if (isInt(id) && this.vueRouterOptions.some(i => i.id === id)) {
        return id
      } else {
        return 0
      }
    },
    async onSubmit() {
      if (this.editMode) {
        await updateVueRouter(this.resourceId, this.form)
        this.$router.back()
      } else {
        await storeVueRouter(this.form)
        this.$router.push('/vue-routers')
      }

      this.$message.success(getMessage('saved'))
    },
    async getData() {
      const [
        { data: vueRouters },
        { data: roles },
        { data: permissions },
      ] = await Promise.all([
        getVueRouters({ except: this.resourceId }),
        getAdminRoles({ all: 1 }),
        getAdminPerms({ all: 1 }),
      ])

      this.vueRouters = vueRouters
      !this.editMode && (this.form.parent_id = this.queryParentId())
      this.roles = roles
      this.permissions = permissions

      if (this.editMode) {
        const { data } = await editVueRouter(this.resourceId)
        this.fillForm(data)
      }
    },
  },
}
</script>

<style lang="scss">
.icon {
  .el-select .el-input {
    width: 80px;
  }
}
</style>
