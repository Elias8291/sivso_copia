export default function FilterBar({ children }) {
    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
            {children}
        </div>
    );
}
