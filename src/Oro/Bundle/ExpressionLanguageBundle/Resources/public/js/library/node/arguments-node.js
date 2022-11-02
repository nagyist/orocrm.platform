import ArrayNode from './array-node';

class ArgumentsNode extends ArrayNode {
    /**
     * @inheritDoc
     */
    evaluate(functions, values) {
        return this.getKeyValuePairs()
            .map(pair => pair.value.evaluate(functions, values));
    }

    /**
     * @inheritDoc
     */
    compile(compiler) {
        this.compileArguments(compiler, false);
    }
}

export default ArgumentsNode;
