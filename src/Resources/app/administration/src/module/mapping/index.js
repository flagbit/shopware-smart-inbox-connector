import template from "./eins-und-eins-config-mapping.html.twig"
const { Criteria } = Shopware.Data;

Shopware.Component.register('eins-und-eins-config-mapping', {
    inject: ['repositoryFactory'],

    template,

    created() {
        this.initStates();
    },

    methods: {
        initStates() {
            const savedStates = this.getStateValues();
            const currentStates = {};
            // TODO Use assosiations for data structure
            this.searchForAll('state_machine')
                .then((stateMachines) => {
                    const stateMachineList = {};
                    stateMachines.forEach(stateMachine => {
                        const technicalName = stateMachine.technicalName;
                        const name = stateMachine.name;

                        stateMachineList[stateMachine.id] = { key: technicalName, value: name };
                    });

                    return stateMachineList;
                })
                .then((stateMachines) => {
                    this.searchForAll('state_machine_state')
                        .then((states) => {
                            states.forEach(state => {
                                const technicalName = stateMachines[state.stateMachineId].key + "-" + state.technicalName;
                                const name = stateMachines[state.stateMachineId].value + ": " + state.name;

                                const option = {
                                    key: technicalName,
                                    value: name,
                                    selection: savedStates[technicalName] || '',
                                    onSelect: (selectedValue) => {
                                        this.setChoice(selectedValue, technicalName);
                                    },
                                };

                                currentStates[technicalName] = option;
                            });

                            this.$data.states = currentStates;
                        });
                });
        },

        searchForAll(entityName) {
            const criteria = new Criteria();
            criteria.setLimit(100);

            const stateMachineRepository = this.repositoryFactory.create(entityName);

            return stateMachineRepository
                .search(criteria, Shopware.Context.api)
        },

        setChoice(selectedValue, key) {
            this.$data.states[key].selection = selectedValue;

            this.setStateValue(key, selectedValue);
        },

        getStateValues() {
            // TODO Improve data handling for this component
            return this.$parent.$parent.$parent.actualConfigData.null[this.$attrs.name] || {};
        },

        setStateValue(key, state) {
            const stateConfig = this.getStateValues();
            stateConfig[key] = state;

            // TODO Improve data handling for this component
            this.$parent.$parent.$parent.actualConfigData.null[this.$attrs.name] = stateConfig;
        },
    },

    data() {
        // TODO Try to get states from schemaorg-email-body library
        const options = [
            {label: 'OrderCancelled', value: 'OrderCancelled'},
            {label: 'OrderDelivered', value: 'OrderDelivered'},
            {label: 'OrderInTransit', value: 'OrderInTransit'},
            {label: 'OrderPaymentDue', value: 'OrderPaymentDue'},
            {label: 'OrderPickupAvailable', value: 'OrderPickupAvailable'},
            {label: 'OrderProblem', value: 'OrderProblem'},
            {label: 'OrderProcessing', value: 'OrderProcessing'},
            {label: 'OrderReturned', value: 'OrderReturned'},
        ];

        return { states: {}, options: options, label: this.$attrs.label };
    },
});
