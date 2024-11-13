<script setup>
import { klantStore } from '../../store/store.js'
</script>

<template>
	<div class="personenContainer">
		<div class="itemContainer">
			<NcDashboardWidget :items="items"
				:loading="loading"
				@show="onShow">
				<template #empty-content>
					<NcEmptyContent name="Geen personen gevonden">
						<template #icon>
							<AccountOutline />
						</template>
					</NcEmptyContent>
				</template>
			</NcDashboardWidget>
		</div>

		<div class="searchContainer">
			<NcTextField :disabled="loading"
				label="Zoeken op voornaam"
				maxlength="255"
				class="searchField"
				:value.sync="searchPerson" />

			<NcButton type="primary"
				:disabled="loading"
				class="searchButton"
				@click="search">
				<template #icon>
					<Search :size="20" />
				</template>
				Zoeken
			</NcButton>
		</div>
	</div>
</template>

<script>
// Components
import { NcDashboardWidget, NcEmptyContent, NcButton, NcTextField } from '@nextcloud/vue'
import Search from 'vue-material-design-icons/Magnify.vue'
import AccountOutline from 'vue-material-design-icons/AccountOutline.vue'

export default {
	name: 'PersonenWidget',

	components: {
		NcDashboardWidget,
		NcEmptyContent,
		NcButton,
		Search,
		NcTextField,
		AccountOutline,
	},

	data() {
		return {
			loading: false,
			isModalOpen: false,
			personenItems: [],
			searchPerson: '',
		}
	},

	computed: {
		items() {
			return this.personenItems
		},
	},

	mounted() {
		this.fetchPersonenItems()
	},

	methods: {
		fetchPersonenItems() {
			this.loading = true
			klantStore.searchPersons()
				.then(() => {
					this.personenItems = klantStore.klantenList.map(person => ({
						id: person.id,
						mainText: `${person.voornaam} ${person.tussenvoegsel} ${person.achternaam}`,
						subText: person.emailadres,
						avatarUrl: '/apps-extra/zaakafhandelapp/img/account-outline.svg',
					}))
					this.loading = false
				})
		},
		search() {
			this.loading = true
			klantStore.searchPersons(this.searchPerson)
				.then(() => {
					this.personenItems = klantStore.klantenList.map(person => ({
						id: person.id,
						mainText: `${person.voornaam} ${person.tussenvoegsel} ${person.achternaam}`,
						subText: person.emailadres,
						avatarUrl: '/apps-extra/zaakafhandelapp/img/account-outline.svg',
					}))
					this.loading = false
				})
				.finally(() => {
					this.loading = false
				})
		},
		onShow() {
			window.open('/apps/opencatalogi/catalogi', '_self')
		},
	},

}
</script>
<style scoped>
.personenContainer {
  display: flex;
  justify-content: space-between;
  flex-direction: column;
  height: 100%;
}
.itemContainer {
  overflow: auto;
  margin-block-end: var(--zaa-margin-10);
}
.searchContainer {
  display: flex;
  align-items: end;
  gap: 10px;
}
.searchField {
  width: auto;
}
.searchButton {
  min-width: min-content !important;
}
</style>
