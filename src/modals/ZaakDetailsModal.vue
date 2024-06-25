<template>
    <NcModal v-if="isModalOpen.zaakDetailsModal" ref="modalRef" @close="closeModal">
        <div class="modal__content">
            <h2>Zaak aanmaken</h2>
            <div class="form-group">
                <NcTextField label="Identificatie" :value.sync="identificatie" />
            </div>
            <div class="form-group">
                <NcTextField label="Omschrijving" :value.sync="omschrijving" />
            </div>
            <div class="form-group">
                <NcTextArea label="Toelichting" :value.sync="toelichting" />
            </div>
            <div class="form-group">
                <NcTextField label="Bronorganisatie" :value.sync="bronorganisatie" />
            </div>
            <div class="form-group">
                <NcTextField label="VerantwoordelijkeOrganisatie" :value.sync="verantwoordelijkeOrganisatie" />
            </div>
            <div class="form-group">
                <NcTextField label="Zaaktype" :value.sync="zaaktype" />
            </div>
            <div class="form-group">
                <NcTextField label="Archiefstatus" :value.sync="archiefstatus" />
            </div>
            <div class="form-group">
                <NcTextField label="Startdatum" :value.sync="startdatum" />
            </div>
            <NcButton
                :disabled="!identificatie || !omschrijving || !toelichting || !bronorganisatie || !verantwoordelijkeOrganisatie || !zaaktype || !archiefstatus || !startdatum"
                @click="addZaak" type="primary">
                Submit
            </NcButton>
        </div>
    </NcModal>
</template>

<script>
import { NcButton, NcModal, NcTextField, NcTextArea } from '@nextcloud/vue';
import { TEMP_AUTHORIZATION_KEY } from '../data/TempAuthKey';
import { isModalOpen } from './modalContext';

export default {
    name: "ZaakDetailsModal",
    props: {
        modalOpen: {
            type: Boolean,
            required: true
        },
        closeAction: {
            type: Function,
            required: true
        },
        submitAction: {
            type: Function,
            required: true
        },
    },
    data() {
        return {
            isModalOpen,
            identificatie: '',
            omschrijving: '',
            toelichting: '',
            bronorganisatie: '',
            verantwoordelijkeOrganisatie: '',
            zaaktype: '',
            archiefstatus: '',
            startdatum: '',
        }
    },
    components: {
        NcModal,
        NcTextField,
        NcTextArea,
        NcButton
    },
    methods: {
        addZaak() {
            fetch(
                `https://api.test.common-gateway.commonground.nu/api/zrc/v1/zaken`,
                {
                    method: 'POST',
                    headers: {
                        "Authorization": TEMP_AUTHORIZATION_KEY
                    },
                    body: JSON.stringify({
                        identificatie: this.identificatie,
                        omschrijving: this.omschrijving,
                        toelichting: this.toelichting,
                        bronorganisatie: this.bronorganisatie,
                        verantwoordelijkeOrganisatie: this.verantwoordelijkeOrganisatie,
                        zaaktype: this.zaaktype,
                        archiefstatus: this.archiefstatus,
                        startdatum: this.startdatum
                    })
                },
            )
                .then((response) => {
                    console.log(response)
                })
                .catch((err) => {
                    console.error(err)
                })
        },
        closeModal() {
            isModalOpen.zaakDetailsModal = false
        },
    }
}
</script>

<style>
.modal__content {
    margin: var(--zaa-margin-50);
    text-align: center;
}

.zaakDetailsContainer {
    margin-block-start: var(--zaa-margin-20);
    margin-inline-start: var(--zaa-margin-20);
    margin-inline-end: var(--zaa-margin-20);
}
</style>
